<?php

namespace App\Services\Verification;

use App\Models\ApiCenter;
use App\Models\CustomApi;
use App\Models\SystemSetting;
use App\Services\DataVerify\DataVerifyClient;
use App\Services\Vuvaa\VuvaaClient;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmartSwitcherService
{
    private const CIRCUIT_BREAKER_MINUTES = 3;

    /**
     * Revert Vuvaa in database and settings to working demo credentials.
     */
    public static function revertVuvaaToDemo(): void
    {
        try {
            // 1. Reset SystemSettings if set to live
            $sysUser = (string) SystemSetting::get('vuvaa_username', '');
            if ($sysUser === 'fuwalive' || str_contains($sysUser, 'live')) {
                SystemSetting::set('vuvaa_username', 'fuwa_demo_0417190741', 'integrations');
                SystemSetting::set('vuvaa_password', 'Password', 'integrations');
                SystemSetting::set('vuvaa_endpoint', 'https://premiere.vuvaa.com/demo/NIN_Validation_LIVE', 'integrations');
                SystemSetting::set('vuvaa_encryption_key', 'FD!-F=15B46BAD21', 'integrations');
                SystemSetting::set('vuvaa_encryption_iv', '0123456789012345', 'integrations');
            }

            // 2. Reset CustomApi record in DB
            $vuvaaApi = CustomApi::where('provider_identifier', 'vuvaa')->first();
            if ($vuvaaApi) {
                $cfg = is_array($vuvaaApi->config) ? $vuvaaApi->config : [];
                $cfg['username'] = 'fuwa_demo_0417190741';
                $cfg['password'] = 'Password';
                $cfg['encryption_key'] = 'FD!-F=15B46BAD21';
                $cfg['encryption_iv'] = '0123456789012345';
                $cfg['reason'] = $cfg['reason'] ?? 'nyscCheck';

                $vuvaaApi->update([
                    'endpoint' => 'https://premiere.vuvaa.com/demo/NIN_Validation_LIVE',
                    'config' => $cfg,
                    'status' => true,
                ]);
            }

            // 3. Invalidate any cached auth tokens
            Cache::forget('vuvaa.token.' . md5('fuwalive'));
            Cache::forget('vuvaa.token.' . md5('fuwa_demo_0417190741'));
            Cache::forget('vuvaa.down');
            if ($vuvaaApi) {
                Cache::forget('provider.fail.count.' . $vuvaaApi->id);
            }
        } catch (\Throwable $e) {
            Log::warning('SmartSwitcherService: revertVuvaaToDemo failed: ' . $e->getMessage());
        }
    }

    /**
     * Get candidate providers sorted intelligently by health and priority.
     */
    public function getCandidateProviders(string $mode, ?int $preferredProviderId = null): Collection
    {
        $query = CustomApi::whereIn('service_type', [
            'nin',
            'nin_verification',
            'nin_face_verification',
            'nin_validation',
            'validation',
            'identity',
        ])
        ->where('status', true);

        if (in_array($mode, ['validation', 'validation_status'], true)) {
            $query->where('provider_identifier', 'robosttech');
        }

        $allProviders = $query->get()->filter(fn (CustomApi $c) => $this->providerSupportsMode($c, $mode))->values();

        // Calculate dynamic health score:
        // Providers with recent technical failures in the circuit breaker get de-prioritized to the end
        $sorted = $allProviders->sortBy(function (CustomApi $p) {
            $failCount = (int) Cache::get('provider.fail.count.' . $p->id, 0);
            $penalty = $failCount > 0 ? (1000 + $failCount * 50) : 0;
            return ((int) $p->priority) + $penalty;
        })->values();

        if ($preferredProviderId) {
            $preferred = $allProviders->firstWhere('id', $preferredProviderId);
            if ($preferred) {
                return collect([$preferred])
                    ->concat($sorted->reject(fn (CustomApi $c) => $c->id === $preferred->id))
                    ->values();
            }
        }

        return $sorted;
    }

    /**
     * Execute NIN verification across available providers with smart failover.
     */
    public function verifyNin(
        Request $request,
        string $mode,
        ?CustomApi $preferredProvider = null,
        ?array $selfieMeta = null
    ): array {
        $candidates = $this->getCandidateProviders($mode, $preferredProvider?->id);

        $errors = [];
        $attempted = [];
        $identityErrors = [];

        foreach ($candidates as $provider) {
            $attempted[] = $provider->name;
            try {
                $res = $this->callProvider($provider, $request, $mode, $selfieMeta);

                if (!empty($res['status']) && !empty($res['data'])) {
                    // Provider succeeded! Mark healthy
                    $this->recordProviderSuccess($provider);

                    $normalizedData = $this->normalizeIdentityData(
                        $res['data'],
                        $mode,
                        (string) ($request->input('number') ?: $request->input('share_code'))
                    );

                    return [
                        'status' => true,
                        'message' => $res['message'] ?? 'Verification Successful',
                        'data' => $normalizedData,
                        'provider' => $provider->name,
                        'attempted_providers' => $attempted,
                    ];
                }

                $errMsg = (string) ($res['message'] ?? 'Verification failed');
                $errors[] = $provider->name . ': ' . $errMsg;

                if ($this->isTechnicalError($errMsg)) {
                    $this->recordProviderFailure($provider, $errMsg);
                    Log::warning("SmartSwitcher: {$provider->name} technical failure on mode [{$mode}]. Auto-switching to next API.", [
                        'error' => $errMsg,
                    ]);
                } else {
                    $identityErrors[] = $errMsg;
                }

            } catch (\Throwable $e) {
                $this->recordProviderFailure($provider, $e->getMessage());
                $errors[] = $provider->name . ': ' . $e->getMessage();
                Log::warning("SmartSwitcher: {$provider->name} exception on mode [{$mode}]. Auto-switching to next API.", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Fallback to Legacy DataVerify API Center if available and custom providers failed
        if (!in_array($mode, ['validation', 'validation_status', 'selfie', 'share_code', 'requery'], true)) {
            $apiCenter = ApiCenter::first();
            $apiKey = $apiCenter?->dataverify_api_key ?: env('DATAVERIFY_API_KEY') ?: SystemSetting::get('dataverify_api_key');

            if ($apiKey) {
                $attempted[] = 'Legacy DataVerify Center';
                try {
                    $legacyRes = $this->callLegacyDataVerify($apiCenter, $apiKey, $request, $mode);
                    if (!empty($legacyRes['status']) && !empty($legacyRes['data'])) {
                        $normalizedData = $this->normalizeIdentityData(
                            $legacyRes['data'],
                            $mode,
                            (string) $request->input('number')
                        );

                        return [
                            'status' => true,
                            'message' => 'Verification Successful',
                            'data' => $normalizedData,
                            'provider' => 'DataVerify Legacy API',
                            'attempted_providers' => $attempted,
                        ];
                    }
                    if (!empty($legacyRes['message'])) {
                        $errors[] = 'Legacy API: ' . $legacyRes['message'];
                    }
                } catch (\Throwable $e) {
                    $errors[] = 'Legacy API: ' . $e->getMessage();
                }
            }
        }

        // If all providers failed, format intelligent message
        if (!empty($identityErrors)) {
            // Prioritize clear user messages (e.g. "NIN not found" or "No record found")
            return [
                'status' => false,
                'message' => $identityErrors[0],
                'errors' => $errors,
                'attempted_providers' => $attempted,
            ];
        }

        return [
            'status' => false,
            'message' => 'All available verification API systems are currently unavailable. Please try again shortly.',
            'errors' => $errors,
            'attempted_providers' => $attempted,
        ];
    }

    /**
     * Call individual provider based on its driver/identifier.
     */
    public function callProvider(CustomApi $provider, Request $request, string $mode, ?array $selfieMeta = null): array
    {
        $number = (string) $request->input('number', '');

        // 1. VUVAA Client
        if (VuvaaClient::isVuvaaProvider($provider)) {
            $client = new VuvaaClient($provider);

            if ($mode === 'nin') {
                $res = $client->verifyNin($number);
            } elseif ($mode === 'selfie') {
                $selfieImage = $selfieMeta['stored_selfie_base64'] ?? (string) $request->input('selfie');
                $res = $client->verifyInPerson($number, $selfieImage);
            } elseif ($mode === 'share_code') {
                $res = $client->verifyShareCode((string) $request->input('share_code'));
            } elseif ($mode === 'requery') {
                $res = $client->requery((string) $request->input('reference_id'));
            } else {
                return ['status' => false, 'message' => "Mode [{$mode}] not supported by VUVAA."];
            }

            return [
                'status' => $res['ok'] ?? false,
                'message' => $res['message'] ?? 'VUVAA response error',
                'data' => $res['data'] ?? [],
                'provider' => $provider->name,
            ];
        }

        // 2. DataVerify Client
        if (DataVerifyClient::isDataVerifyProvider($provider)) {
            $client = new DataVerifyClient($provider);

            if ($mode === 'nin') {
                $res = $client->verifyNin($number);
            } elseif ($mode === 'phone') {
                $phone = (string) ($request->input('phone') ?: $number);
                $res = $client->verifyPhone($phone);
            } elseif ($mode === 'demographic') {
                $res = $client->verify('demographic', [
                    'firstname' => (string) $request->input('firstname'),
                    'lastname' => (string) $request->input('lastname'),
                    'dob' => (string) $request->input('dob'),
                    'gender' => (string) ($request->input('gender') ?? ''),
                ]);
            } else {
                return ['status' => false, 'message' => "Mode [{$mode}] not supported by DataVerify."];
            }

            return [
                'status' => $res['ok'] ?? false,
                'message' => $res['message'] ?? 'DataVerify response error',
                'data' => $res['data'] ?? [],
                'provider' => $provider->name,
            ];
        }

        // 3. RobostTech Client
        if (strtolower((string) $provider->provider_identifier) === 'robosttech') {
            return $this->callRobostTech($provider, $request, $mode);
        }

        // 4. VerifyMe Client
        if (strtolower((string) $provider->provider_identifier) === 'verifyme') {
            return $this->callVerifyMe($provider, $request, $mode);
        }

        // 5. Generic CustomApi POST endpoint
        if (!empty($provider->endpoint)) {
            return $this->callGenericHttpProvider($provider, $request, $mode);
        }

        return ['status' => false, 'message' => "Provider [{$provider->name}] is not configured."];
    }

    /**
     * RobostTech HTTP caller.
     */
    private function callRobostTech(CustomApi $provider, Request $request, string $mode): array
    {
        $modePath = [
            'nin' => 'nin_verify',
            'phone' => 'nin_phone',
            'demographic' => 'nin_demo',
            'tracking' => 'validation_status',
            'validation' => 'validation',
            'validation_status' => 'validation_status',
            'clearance' => 'clearance',
            'clearance_status' => 'clearance_status',
        ];
        $path = $modePath[$mode] ?? 'nin_verify';
        $endpoint = rtrim((string) $provider->endpoint, '/');
        $url = str_ends_with($endpoint, $path) ? $endpoint : ($endpoint . '/' . $path);

        $headers = is_array($provider->headers) ? $provider->headers : [];
        $headers['Content-Type'] = 'application/json';

        $apiKey = $provider->api_key ?: env('ROBOSTTECH_API_KEY') ?: SystemSetting::get('robosttech_api_key');
        if (!empty($apiKey) && empty($headers['api-key'])) {
            $headers['api-key'] = (string) $apiKey;
        }

        $number = trim((string) $request->input('number', ''));
        $payload = [];

        if (in_array($mode, ['nin', 'tracking', 'validation', 'validation_status'], true)) {
            $payload = ['nin' => $number];
            if ($mode === 'validation' && $request->filled('validation_reason')) {
                $payload['reason'] = trim((string) $request->input('validation_reason'));
                $payload['validation_reason'] = $payload['reason'];
            }
        } elseif ($mode === 'phone') {
            $payload = ['phone' => trim((string) ($request->input('phone') ?: $number))];
        } elseif ($mode === 'demographic') {
            $payload = [
                'firstname' => strtoupper(trim((string) $request->input('firstname'))),
                'lastname' => strtoupper(trim((string) $request->input('lastname'))),
                'middlename' => strtoupper(trim((string) $request->input('middlename', ''))),
                'gender' => strtolower(trim((string) $request->input('gender'))),
                'dateOfBirth' => trim((string) $request->input('dob')),
            ];
        } elseif ($mode === 'clearance' || $mode === 'clearance_status') {
            $payload = ['tracking_id' => trim((string) ($request->input('tracking_id') ?: $number))];
        }

        $res = Http::timeout((int) ($provider->timeout_seconds ?: 45))
            ->acceptJson()
            ->asJson()
            ->withHeaders($headers)
            ->post($url, $payload);

        $json = $res->json();
        if (!$res->successful() || !is_array($json)) {
            $msg = is_array($json) ? ($json['message'] ?? $json['detail'] ?? 'RobostTech request failed') : 'RobostTech HTTP ' . $res->status();
            return ['status' => false, 'message' => is_string($msg) ? $msg : json_encode($msg)];
        }

        $st = strtolower((string) ($json['status'] ?? ''));
        $ok = $st === 'success' || $st === 'true' || $st === 'ok' || ($json['status'] ?? null) === true;

        if (!$ok) {
            return ['status' => false, 'message' => (string) ($json['message'] ?? 'RobostTech verification failed')];
        }

        return [
            'status' => true,
            'message' => 'Verification Successful',
            'data' => $json['data'] ?? $json,
            'provider' => $provider->name,
        ];
    }

    /**
     * VerifyMe HTTP caller.
     */
    private function callVerifyMe(CustomApi $provider, Request $request, string $mode): array
    {
        $apiKey = $provider->api_key ?: env('VERIFYME_API_KEY') ?: SystemSetting::get('verifyme_api_key');
        if (empty($apiKey)) {
            return ['status' => false, 'message' => 'VerifyMe API key is not configured.'];
        }

        $url = (string) $provider->endpoint;
        $headers = [
            'Authorization' => 'Bearer ' . $apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        $number = trim((string) $request->input('number', ''));
        $payload = ['nin' => $number];

        if ($mode === 'phone') {
            $payload = ['phone' => trim((string) ($request->input('phone') ?: $number))];
        } elseif ($mode === 'demographic') {
            $payload = [
                'firstname' => (string) $request->input('firstname'),
                'lastname' => (string) $request->input('lastname'),
                'dob' => (string) $request->input('dob'),
            ];
        }

        $res = Http::timeout((int) ($provider->timeout_seconds ?: 45))
            ->withHeaders($headers)
            ->post($url, $payload);

        $json = $res->json();
        if (!$res->successful() || !is_array($json)) {
            return ['status' => false, 'message' => is_array($json) ? ($json['message'] ?? 'VerifyMe request failed') : 'VerifyMe HTTP ' . $res->status()];
        }

        $st = strtolower((string) ($json['status'] ?? ''));
        $ok = $st === 'success' || ($json['status'] ?? null) === true;

        if (!$ok) {
            return ['status' => false, 'message' => (string) ($json['message'] ?? 'VerifyMe verification failed')];
        }

        return [
            'status' => true,
            'message' => 'Verification Successful',
            'data' => $json['data'] ?? $json,
            'provider' => $provider->name,
        ];
    }

    /**
     * Generic CustomApi HTTP POST caller.
     */
    private function callGenericHttpProvider(CustomApi $provider, Request $request, string $mode): array
    {
        $headers = is_array($provider->headers) ? $provider->headers : [];
        if (!empty($provider->api_key) && empty($headers['Authorization']) && empty($headers['api-key'])) {
            $headers['Authorization'] = 'Bearer ' . $provider->api_key;
        }

        $res = Http::timeout((int) ($provider->timeout_seconds ?: 45))
            ->withHeaders($headers)
            ->post($provider->endpoint, $request->all());

        $json = $res->json();
        if (!$res->successful() || !is_array($json)) {
            return ['status' => false, 'message' => 'Generic provider returned error: ' . $res->status()];
        }

        $st = strtolower((string) ($json['status'] ?? ''));
        $ok = $st === 'success' || $st === 'true' || ($json['status'] ?? null) === true;

        return [
            'status' => $ok,
            'message' => (string) ($json['message'] ?? ($ok ? 'Success' : 'Failed')),
            'data' => $json['data'] ?? $json,
            'provider' => $provider->name,
        ];
    }

    /**
     * Legacy DataVerify caller from api_centers.
     */
    private function callLegacyDataVerify(?ApiCenter $apiCenter, string $apiKey, Request $request, string $mode): array
    {
        $endpoint = $apiCenter?->dataverify_endpoint_nin ?: 'https://dataverify.org/developers/nin_api/';
        $number = (string) $request->input('number', '');

        $payload = ['api_key' => $apiKey];
        if ($mode === 'phone') {
            $payload['phone'] = (string) ($request->input('phone') ?: $number);
        } else {
            $payload['nin'] = $number;
        }

        $res = Http::timeout(45)->asJson()->post($endpoint, $payload);
        $json = $res->json();

        if (!$res->successful() || !is_array($json)) {
            return ['status' => false, 'message' => 'Legacy API error: ' . $res->status()];
        }

        $statusVal = strtolower((string) ($json['status'] ?? ''));
        $code = (string) ($json['response_code'] ?? '');
        $ok = $statusVal === 'success' || $statusVal === 'true' || $code === '00' || ($json['status'] ?? null) === true;

        if (!$ok) {
            return ['status' => false, 'message' => (string) ($json['message'] ?? 'Legacy API verification failed')];
        }

        return [
            'status' => true,
            'message' => 'Verification Successful',
            'data' => $json['data'] ?? ($json['user_data'] ?? $json),
        ];
    }

    /**
     * Check if a provider supports the given mode.
     */
    public function providerSupportsMode(CustomApi $provider, string $mode): bool
    {
        if (VuvaaClient::isVuvaaProvider($provider)) {
            return in_array($mode, ['nin', 'selfie', 'share_code', 'requery'], true);
        }

        $modes = is_array($provider->supported_modes) ? $provider->supported_modes : [];
        if (!empty($modes)) {
            return in_array($mode, $modes, true);
        }

        if (strtolower((string) $provider->provider_identifier) === 'robosttech') {
            return in_array($mode, ['nin', 'phone', 'demographic', 'tracking', 'validation', 'validation_status', 'clearance'], true);
        }

        if (strtolower((string) $provider->provider_identifier) === 'dataverify') {
            return in_array($mode, ['nin', 'phone', 'demographic', 'tracking'], true);
        }

        return $provider->service_type === 'nin_face_verification'
            ? $mode === 'selfie'
            : in_array($mode, ['nin', 'phone', 'demographic', 'tracking', 'vnin'], true);
    }

    /**
     * Circuit breaker: record provider technical failure.
     */
    public function recordProviderFailure(CustomApi $provider, string $error): void
    {
        $key = 'provider.fail.count.' . $provider->id;
        $count = (int) Cache::get($key, 0) + 1;
        Cache::put($key, $count, now()->addMinutes(self::CIRCUIT_BREAKER_MINUTES));
    }

    /**
     * Reset failure counter on success.
     */
    public function recordProviderSuccess(CustomApi $provider): void
    {
        Cache::forget('provider.fail.count.' . $provider->id);
    }

    /**
     * Check if error indicates an infrastructure/auth failure that warrants immediate failover.
     */
    public function isTechnicalError(string $message): bool
    {
        $lower = strtolower($message);

        $technicalPatterns = [
            'curl error',
            'could not resolve host',
            'connection timed out',
            'failed to connect',
            'unauthorized',
            'authentication failed',
            're-authentication failed',
            'invalid token',
            'token expired',
            '401',
            '500',
            '502',
            '503',
            '504',
            'bad gateway',
            'service unavailable',
            'insufficient',
            'wallet',
            'unit',
            'invalid api key',
            'ip address',
            'decryption failed',
            'missing payload',
            'sqlstate',
            'exception',
        ];

        foreach ($technicalPatterns as $pattern) {
            if (str_contains($lower, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalizes identity JSON shapes across all providers into Fuwa.NG's unified schema.
     */
    public function normalizeIdentityData(array $raw, string $mode, ?string $inputNumber = null): array
    {
        $data = $raw;
        if (isset($data['data']) && is_array($data['data'])) {
            $data = array_merge($data, $data['data']);
        }
        if (isset($data['user_data']) && is_array($data['user_data'])) {
            $data = array_merge($data, $data['user_data']);
        }

        $firstname = $data['firstname'] ?? $data['firstName'] ?? $data['first_name'] ?? null;
        $surname = $data['surname'] ?? $data['lastName'] ?? $data['last_name'] ?? $data['lastname'] ?? null;
        $middlename = $data['middlename'] ?? $data['middleName'] ?? $data['middle_name'] ?? null;
        $telephoneno = $data['telephoneno'] ?? $data['phone1'] ?? $data['phone'] ?? $data['phone_number'] ?? $data['mobile'] ?? null;
        $birthdate = $data['birthdate'] ?? $data['dateOfBirth'] ?? $data['dob'] ?? $data['birth_date'] ?? null;
        $residenceAddress = $data['residence_address'] ?? $data['residence_AdressLine1'] ?? $data['residenceAddressLine1'] ?? $data['address'] ?? null;
        $residenceState = $data['residence_state'] ?? $data['residenceTown'] ?? $data['state_of_origin'] ?? null;
        $gender = !empty($data['gender']) ? strtoupper((string) $data['gender']) : (!empty($data['sex']) ? strtoupper((string) $data['sex']) : null);
        $nin = $data['nin'] ?? $data['national_identity_number'] ?? $data['vnin'] ?? ($inputNumber && strlen($inputNumber) === 11 ? $inputNumber : null);
        $photo = $data['photo'] ?? $data['image'] ?? $data['selfie'] ?? $data['picture'] ?? null;

        $normalized = array_merge($data, [
            'firstname' => $firstname,
            'surname' => $surname,
            'middlename' => $middlename,
            'telephoneno' => $telephoneno,
            'birthdate' => $birthdate,
            'residence_address' => $residenceAddress,
            'residence_state' => $residenceState,
            'gender' => $gender,
            'nin' => $nin,
            'photo' => $photo,
        ]);

        return $normalized;
    }
}
