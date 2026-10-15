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
            // 0. Ensure Validation and IPE modes default to robosttech
            $currValMode = SystemSetting::get('nin_validation_mode');
            if ($currValMode === null || $currValMode === 'manual') {
                SystemSetting::set('nin_validation_mode', 'robosttech', 'services');
            }

            $currIpeMode = SystemSetting::get('ipe_clearance_mode');
            if ($currIpeMode === null || $currIpeMode === 'manual') {
                SystemSetting::set('ipe_clearance_mode', 'robosttech', 'services');
            }

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

            // 4. Ensure DataVerify API Key is synced across DB & Settings
            $dvKey = 'dataverify57b5ef07cb66caaadee990a6aa733ede';
            SystemSetting::set('dataverify_api_key', $dvKey, 'integrations');
            $dataVerifyApi = CustomApi::where('provider_identifier', 'dataverify')->first();
            if ($dataVerifyApi) {
                $dataVerifyApi->update([
                    'api_key' => $dvKey,
                    'status' => true,
                ]);
                Cache::forget('provider.fail.count.' . $dataVerifyApi->id);
            }
            $apiCenter = ApiCenter::first();
            if ($apiCenter && $apiCenter->dataverify_api_key !== $dvKey) {
                $apiCenter->update(['dataverify_api_key' => $dvKey]);
            }
        } catch (\Throwable $e) {
            Log::warning('SmartSwitcherService::revertVuvaaToDemo failed: ' . $e->getMessage());
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
            'clearance',
            'ipe_clearance',
        ])
        ->where('status', true);

        if (in_array($mode, ['validation', 'validation_status', 'clearance', 'clearance_status', 'ipe', 'ipe_clearance'], true)) {
            $query->whereIn('provider_identifier', ['robosttech', 'dataverify']);
        }

        $allProviders = $query->get()->filter(fn (CustomApi $c) => $this->providerSupportsMode($c, $mode))->values();

        // Calculate dynamic health score:
        // Providers with recent technical failures in the circuit breaker get de-prioritized to the end
        // Validation and IPE modes prioritize whichever provider has active credentials
        $sorted = $allProviders->sortBy(function (CustomApi $p) use ($mode) {
            $isRobost = strtolower((string) $p->provider_identifier) === 'robosttech';
            $isDv = strtolower((string) $p->provider_identifier) === 'dataverify' || str_contains(strtolower((string) $p->endpoint), 'dataverify');
            $modeBonus = 0;
            if (in_array($mode, ['validation', 'validation_status', 'clearance', 'clearance_status', 'ipe', 'ipe_clearance'], true)) {
                $apiCenter = ApiCenter::first();
                $hasKey = false;
                if ($isDv) {
                    $hasKey = !empty($p->api_key) || !empty($apiCenter?->dataverify_api_key) || !empty(env('DATAVERIFY_API_KEY')) || !empty(\App\Models\SystemSetting::get('dataverify_api_key'));
                } elseif ($isRobost) {
                    $hasKey = !empty($p->api_key) || !empty($apiCenter?->robosttech_api_key) || !empty(env('ROBOSTTECH_API_KEY')) || !empty(\App\Models\SystemSetting::get('robosttech_api_key'));
                }
                $modeBonus = $hasKey ? -5000 : 5000;
            }

            $failCount = (int) Cache::get('provider.fail.count.' . $p->id, 0);
            $penalty = $failCount > 0 ? (1000 + $failCount * 50) : 0;
            return ((int) $p->priority) + $penalty + $modeBonus;
        })->unique(function (CustomApi $p) {
            return strtolower((string) ($p->provider_identifier ?: $p->endpoint ?: $p->id));
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
        $isExplicitSelection = $request->filled('api_provider_id');

        if ($isExplicitSelection && $preferredProvider) {
            $candidates = collect([$preferredProvider]);
        } else {
            $candidates = $this->getCandidateProviders($mode, $preferredProvider?->id);
        }

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

                    // If it is a terminal identity error (record not found, invalid NIN, demographic mismatch):
                    // Stop failover immediately unless Vuvaa is in demo mode (where demo returns 'Invalid NIN' for real citizen NINs)
                    $isTerminal = (!empty($res['terminal'])) || $this->isTerminalIdentityError($errMsg);
                    $isVuvaaDemo = VuvaaClient::isVuvaaProvider($provider) && (str_contains(strtolower((string) $provider->endpoint), 'demo') || SystemSetting::get('vuvaa_demo_mode', true));
                    if ($isTerminal && !$isVuvaaDemo) {
                        Log::info("SmartSwitcher: Terminal identity result from {$provider->name} on mode [{$mode}]: '{$errMsg}'. Halting failover to prevent upstream API overcharging.");
                        return [
                            'status' => false,
                            'message' => $errMsg,
                            'errors' => $errors,
                            'attempted_providers' => $attempted,
                            'terminal' => true,
                        ];
                    }
                }

            } catch (\Throwable $e) {
                $this->recordProviderFailure($provider, $e->getMessage());
                $errors[] = $provider->name . ': ' . $e->getMessage();
                Log::warning("SmartSwitcher: {$provider->name} exception on mode [{$mode}]. Auto-switching to next API.", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Fallback to Legacy DataVerify API Center only if DataVerify was NOT already attempted as a custom provider AND no explicit provider was chosen
        $alreadyAttemptedDataVerify = collect($attempted)->contains(function ($name) {
            return str_contains(strtolower((string) $name), 'dataverify');
        });

        if (!$isExplicitSelection && !$alreadyAttemptedDataVerify && !in_array($mode, ['validation', 'validation_status', 'selfie', 'share_code', 'requery'], true)) {
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
                        $identityErrors[] = $legacyRes['message'];
                    }
                } catch (\Throwable $e) {
                    $errors[] = 'Legacy API: ' . $e->getMessage();
                }
            }
        }

        // If a specific preferred provider was explicitly chosen, return its direct error message
        if ($isExplicitSelection && !empty($errors)) {
            $firstError = $errors[0];
            if (str_contains($firstError, ': ')) {
                $firstError = explode(': ', $firstError, 2)[1];
            }
            return [
                'status' => false,
                'message' => $firstError,
                'errors' => $errors,
                'attempted_providers' => $attempted,
            ];
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
            } elseif (in_array($mode, ['tracking', 'tid'], true)) {
                $tid = (string) ($request->input('tracking_id') ?: $request->input('number') ?: $number);
                $res = $client->verifyTracking($tid);
            } elseif ($mode === 'validation') {
                $validationType = (string) ($request->input('validation_reason') ?: $request->input('validation_type') ?: 'no_record_found');
                $normalizedType = match(strtolower($validationType)) {
                    'sim_validation', 'sim', 'sim card', 'sim validation', 'sim card validation' => 'sim_validation',
                    'bank_validation', 'bank', 'bvn', 'bvn validation', 'bank validation' => 'bank_validation',
                    'modification_validation', 'modification', 'data modification', 'name modification' => 'modification_validation',
                    'photographic_error', 'photo', 'photo error', 'photographic error' => 'photographic_error',
                    default => 'no_record_found'
                };
                $res = $client->submitNinValidation($number, $normalizedType);
            } elseif ($mode === 'validation_status') {
                $txId = (string) ($request->input('transaction_id') ?: $request->input('reference_id') ?: '');
                $res = $client->checkNinValidationStatus($number, $txId ?: null);
            } elseif (in_array($mode, ['clearance', 'ipe', 'ipe_clearance'], true)) {
                $trackingId = (string) ($request->input('tracking_id') ?: $request->input('number') ?: $number);
                $res = $client->submitIpeClearance($trackingId);
            } elseif (in_array($mode, ['clearance_status', 'ipe_status'], true)) {
                $trackingId = (string) ($request->input('tracking_id') ?: $request->input('number') ?: $number);
                $txId = (string) ($request->input('transaction_id') ?: $request->input('reference_id') ?: '');
                $res = $client->checkIpeClearanceStatus($trackingId, $txId ?: null);
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
            'ipe' => 'clearance',
            'ipe_clearance' => 'clearance',
        ];
        $path = $modePath[$mode] ?? 'nin_verify';
        $endpoint = rtrim((string) $provider->endpoint, '/');
        $knownActions = [
            'nin_verify',
            'nin_phone',
            'nin_demo',
            'validation_status',
            'validation',
            'clearance_status',
            'clearance',
            'personalization',
            'balance',
        ];
        foreach ($knownActions as $action) {
            if (str_ends_with(strtolower($endpoint), '/' . $action)) {
                $endpoint = substr($endpoint, 0, -strlen('/' . $action));
                break;
            }
        }
        $url = rtrim($endpoint, '/') . '/' . ltrim($path, '/');

        $headers = is_array($provider->headers) ? $provider->headers : [];
        $headers['Content-Type'] = 'application/json';

        $apiKey = $provider->api_key 
            ?: ApiCenter::first()?->robosttech_api_key 
            ?: env('ROBOSTTECH_API_KEY') 
            ?: SystemSetting::get('robosttech_api_key');
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
        } elseif (in_array($mode, ['clearance', 'clearance_status', 'ipe', 'ipe_clearance'], true)) {
            $trackingId = trim((string) ($request->input('tracking_id') ?: $request->input('number') ?: $number));
            $payload = [
                'tracking_id' => $trackingId,
                'number' => $trackingId,
            ];
            if ($request->filled('category')) {
                $payload['category'] = trim((string) $request->input('category'));
            }
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
        $rawMsg = is_array($json) ? ($json['message'] ?? $json['error'] ?? null) : null;
        if (!$res->successful() || !is_array($json)) {
            $msg = $rawMsg ?: ('Generic provider returned error: ' . $res->status());
            if (!str_starts_with($msg, 'Verification Failed:')) {
                $msg = 'Verification Failed: ' . $msg;
            }
            return ['status' => false, 'message' => $msg];
        }

        $st = strtolower((string) ($json['status'] ?? ''));
        $ok = $st === 'success' || $st === 'true' || ($json['status'] ?? null) === true;
        $msg = $rawMsg ?? ($ok ? 'Success' : 'Failed');
        if (!$ok && !str_starts_with($msg, 'Verification Failed:')) {
            $msg = 'Verification Failed: ' . $msg;
        }

        return [
            'status' => $ok,
            'message' => $msg,
            'data' => $json['data'] ?? $json,
            'provider' => $provider->name,
        ];
    }

    /**
     * Legacy DataVerify caller from api_centers.
     */
    private function callLegacyDataVerify(?ApiCenter $apiCenter, string $apiKey, Request $request, string $mode): array
    {
        $rawEndpoint = (string) ($apiCenter?->dataverify_endpoint_nin ?: 'https://dataverify.org/developers/nin_api/');
        $endpoint = DataVerifyClient::normalizeDomain($rawEndpoint) ?: 'https://dataverify.org/developers/nin_api/';
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
            return in_array($mode, ['nin', 'phone', 'demographic', 'tracking', 'validation', 'validation_status', 'clearance', 'clearance_status', 'ipe', 'ipe_clearance'], true);
        }

        if (strtolower((string) $provider->provider_identifier) === 'dataverify') {
            return in_array($mode, ['nin', 'phone', 'demographic', 'tracking', 'validation', 'validation_status', 'clearance', 'clearance_status', 'ipe', 'ipe_clearance'], true);
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

    public function isTerminalIdentityError(string $message): bool
    {
        $lower = strtolower($message);

        $terminalPatterns = [
            'invalid nin',
            'nin not found',
            'record not found',
            'no record found',
            'record does not exist',
            'identity not found',
            'data not found',
            'user not found',
            'not found in database',
            'invalid bvn',
            'bvn not found',
            'invalid vnin',
            'vnin not found',
            'invalid phone',
            'phone not found',
            'phone number not found',
            'face mismatch',
            'selfie does not match',
            'photo mismatch',
            'facial match failed',
            'face verification failed',
            'demographic mismatch',
            'unmatched demographic',
            'tracking id not found',
            'invalid tracking id',
            'tracking not found',
            'tracking id does not exist',
            'does not match any record',
            'no match found',
            'nin has not been registered',
            'unregistered nin',
            'nin does not exist',
            'unregistered',
            'does not exist in database',
        ];

        foreach ($terminalPatterns as $pattern) {
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
