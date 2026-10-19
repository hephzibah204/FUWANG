<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomApi;
use App\Models\VerificationResult;
use App\Services\DataVerify\DataVerifyClient;
use App\Services\PaidActionService;
use App\Services\Vuvaa\VuvaaClient;
use App\Services\VerificationResultService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class VerificationController extends Controller
{
    public function verifyNin(Request $request)
    {
        $request->validate([
            'number' => ['required', 'string'],
            'firstname' => ['nullable', 'string'],
            'lastname' => ['nullable', 'string'],
            'dob' => ['nullable', 'string'],
            'mode' => ['required', 'in:nin,phone,demographic'],
            'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
        ]);

        $user = $request->user();
        
        // --- SMART ROUTING LOGIC ---
        $activeProviders = [];
        if ($request->filled('api_provider_id')) {
            $specificProvider = CustomApi::where('id', $request->api_provider_id)->where('status', true)->first();
            if ($specificProvider) {
                $activeProviders = [$specificProvider];
            }
        } else {
            $activeProviders = CustomApi::query()
                ->whereIn('service_type', ['nin', 'nin_verification'])
                ->where('status', true)
                ->orderBy('priority', 'asc')
                ->get();
        }

        if (count($activeProviders) === 0) {
            return response()->json(['status' => false, 'message' => 'No active provider for NIN verification.'], 503);
        }

        // Use custom price for user if set, or global developer api nin price setting
        $firstProvider = $activeProviders[0];
        $price = (float) $user->getDeveloperApiPrice('developer_api_nin_price', 100.0);

        // Concurrency lock: prevent duplicate debits from rapid API client retries
        $lockKey = "api_nin_lock_{$user->id}_{$request->mode}_" . md5((string) $request->number);
        $lock = Cache::lock($lockKey, 20);

        if (!$lock->get()) {
            return response()->json(['status' => false, 'message' => 'A verification request for this query is already in progress. Please wait.'], 429);
        }

        try {
            $paid = app(PaidActionService::class)->run($user, $price, 'API: NIN Verification', 'NINAPI', function () use ($activeProviders, $request, $user) {
                $errors = [];
                foreach ($activeProviders as $provider) {
                    try {
                        $result = $this->tryProvider($provider, $request, $user);
                        if ($result['status']) {
                            return $result;
                        }
                        $errMsg = (string) ($result['message'] ?? 'Unknown error');
                        $errors[] = $provider->name . ': ' . $errMsg;

                        // If terminal identity error from live provider, halt failover to avoid API overcharge
                        $isTerminal = $this->isTerminalIdentityError($errMsg);
                        $isVuvaaDemo = VuvaaClient::isVuvaaProvider($provider) && (str_contains(strtolower((string) $provider->endpoint), 'demo') || \App\Models\SystemSetting::get('vuvaa_demo_mode', true));
                        if ($isTerminal && !$isVuvaaDemo) {
                            Log::info("API Provider {$provider->name} returned terminal result: '{$errMsg}'. Halting failover to prevent API overcharge.");
                            throw new \RuntimeException($errMsg);
                        }
                    } catch (\Throwable $e) {
                        $msg = $e->getMessage();
                        $lower = strtolower($msg);
                        if (str_contains($lower, 'curl error') || str_contains($lower, 'could not resolve host') || str_contains($lower, 'connection timed out') || str_contains($lower, 'failed to connect')) {
                            $msg = 'Provider service temporarily unavailable.';
                        }
                        $errors[] = $provider->name . ': ' . $msg;
                        Log::warning("API Provider failover: {$provider->name} failed.", ['error' => $e->getMessage()]);

                        if ($this->isTerminalIdentityError($msg)) {
                            throw $e;
                        }
                    }
                }

                // Prioritize user/data errors if present
                $prioritized = null;
                foreach ($errors as $err) {
                    $lowerErr = strtolower($err);
                    if (str_contains($lowerErr, 'invalid nin') || str_contains($lowerErr, 'not found') || str_contains($lowerErr, 'no record')) {
                        $prioritized = str_contains($err, ': ') ? explode(': ', $err, 2)[1] : $err;
                        break;
                    }
                }

                throw new \RuntimeException($prioritized ?: ('All verification providers failed: ' . implode(' | ', $errors)));
            });

            if (!$paid['ok']) {
                $code = isset($paid['txId']) ? 502 : 402;
                return response()->json(['status' => false, 'message' => $paid['message']], $code);
            }

            return response()->json($paid['result']);
        } finally {
            optional($lock)->release();
        }
    }

    private function tryProvider(CustomApi $provider, Request $request, $user): array
    {
        $endpoint = (string) $provider->endpoint;
        $headers = is_array($provider->headers) ? $provider->headers : [];

        if (VuvaaClient::isVuvaaProvider($provider)) {
            if ($request->mode !== 'nin') {
                return ['status' => false, 'message' => 'Provider does not support this mode.'];
            }

            $client = new VuvaaClient($provider);
            $result = $client->verifyNin((string) $request->number);
            if (!$result['ok']) {
                return ['status' => false, 'message' => (string) ($result['message'] ?? 'Verification failed.')];
            }

            $data = $result['data'];
            $vr = app(VerificationResultService::class)->create($user, 'nin_verification', (string) $request->number, (string) $provider->name, $data, 'success');

            return [
                'status' => true,
                'message' => 'NIN verified',
                'result_id' => $vr->id,
                'reference_id' => $vr->reference_id,
                'slip_url' => URL::temporarySignedRoute('services.nin.slip', now()->addDays(7), ['id' => $vr->id, 'type' => 'premium_slip']),
                'data' => $data,
            ];
        }

        if (strtolower((string) $provider->provider_identifier) === 'robosttech') {
            $robost = $this->callRobostTechNin($provider, (string) $request->mode, (string) $request->number);
            if ($robost['status']) {
                $data = $robost['data'] ?? [];
                $vr = app(VerificationResultService::class)->create($user, 'nin_verification', (string) $request->number, (string) $provider->name, $data, 'success');

                return [
                    'status' => true,
                    'message' => $robost['message'] ?? 'NIN verified',
                    'result_id' => $vr->id,
                    'reference_id' => $vr->reference_id,
                    'slip_url' => URL::temporarySignedRoute('services.nin.slip', now()->addDays(7), ['id' => $vr->id, 'type' => 'premium_slip']),
                    'data' => $data,
                ];
            }
            return ['status' => false, 'message' => (string) ($robost['message'] ?? 'Verification failed.')];
        }

        if (DataVerifyClient::isDataVerifyProvider($provider)) {
            $client = new DataVerifyClient($provider);
            $dataverify = $client->verify((string) $request->mode, [
                'number' => (string) $request->number,
                'firstname' => (string) $request->firstname,
                'lastname' => (string) $request->lastname,
                'dob' => (string) $request->dob,
                'gender' => (string) ($request->gender ?? ''),
            ]);
            if ($dataverify['ok']) {
                $data = $dataverify['data'] ?? [];
                $vr = app(VerificationResultService::class)->create($user, 'nin_verification', (string) $request->number, (string) $provider->name, $data, 'success');

                return [
                    'status' => true,
                    'message' => $dataverify['message'] ?? 'NIN verified',
                    'result_id' => $vr->id,
                    'reference_id' => $vr->reference_id,
                    'slip_url' => URL::temporarySignedRoute('services.nin.slip', now()->addDays(7), ['id' => $vr->id, 'type' => 'premium_slip']),
                    'data' => $data,
                ];
            }
            return ['status' => false, 'message' => (string) ($dataverify['message'] ?? 'Verification failed.')];
        }

        // Generic Provider logic
        $slug = $request->mode === 'phone' ? 'nin_phone' : 'nin';
        $url = str_replace('/nin', '/' . $slug, rtrim($endpoint, '/')) . '/' . $request->number;
        $http = Http::timeout(60);
        if (!empty($headers)) {
            $http = $http->withHeaders($headers);
        }

        $response = $http->post($url, [
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'dob' => $request->dob,
        ]);

        $resJson = $response->json() ?? [];
        $resStatus = $resJson['status'] ?? null;
        if ($response->successful() && ($resStatus === 'success' || $resStatus === true || strtolower((string)$resStatus) === 'success')) {
            $data = $response->json()['data'] ?? $response->json();
            $vr = app(VerificationResultService::class)->create($user, 'nin_verification', (string) $request->number, (string) $provider->name, $data, 'success');
            return [
                'status' => true,
                'message' => 'NIN verified',
                'result_id' => $vr->id,
                'reference_id' => $vr->reference_id,
                'slip_url' => URL::temporarySignedRoute('services.nin.slip', now()->addDays(7), ['id' => $vr->id, 'type' => 'premium_slip']),
                'data' => $data,
            ];
        }

        return ['status' => false, 'message' => (string) ($response['message'] ?? 'Verification failed.')];
    }

    private function pickProviderForMode(string $mode): ?CustomApi
    {
        $providers = CustomApi::query()
            ->where('service_type', 'nin_verification')
            ->where('status', true)
            ->orderBy('priority', 'asc')
            ->get();

        if ($providers->isEmpty()) {
            return null;
        }

        if ($mode === 'nin') {
            $vuvaa = $providers->first(fn (CustomApi $p) => VuvaaClient::isVuvaaProvider($p));
            if ($vuvaa) {
                return $vuvaa;
            }
        }

        foreach ($providers as $p) {
            if (VuvaaClient::isVuvaaProvider($p) && $mode !== 'nin') {
                continue;
            }
            return $p;
        }

        return null;
    }

    /**
     * @return array{status:bool,message?:string,data?:array}
     */
    private function callRobostTechNin(CustomApi $provider, string $mode, string $number): array
    {
        $map = [
            'nin' => 'nin_verify',
            'phone' => 'nin_phone',
        ];
        $path = $map[$mode] ?? 'nin_verify';
        $url = $this->resolveRobostEndpoint((string) $provider->endpoint, $path);

        $headers = is_array($provider->headers) ? $provider->headers : [];
        $headers['Content-Type'] = $headers['Content-Type'] ?? 'application/json';
        if (!empty($provider->api_key) && empty($headers['api-key'])) {
            $headers['api-key'] = (string) $provider->api_key;
        }

        $payload = $mode === 'phone'
            ? ['phone' => trim($number)]
            : ['nin' => trim($number)];

        $res = Http::timeout((int) ($provider->timeout_seconds ?: 60))
            ->acceptJson()
            ->asJson()
            ->withHeaders($headers)
            ->post($url, $payload);
        $json = $res->json();
        if (! $res->successful()) {
            return [
                'status' => false,
                'message' => (is_array($json) ? ($json['message'] ?? $json['detail'] ?? null) : null) ?: 'RobostTech verification failed.',
            ];
        }
        if (! is_array($json)) {
            return ['status' => false, 'message' => 'RobostTech returned invalid response.'];
        }

        $statusVal = strtolower((string) ($json['status'] ?? ''));
        $looksSuccessful = $statusVal === 'success' || $statusVal === 'true' || $statusVal === 'ok';
        if (! $looksSuccessful && isset($json['status']) && $json['status'] !== true) {
            return ['status' => false, 'message' => (string) ($json['message'] ?? 'RobostTech verification was not successful.')];
        }

        return [
            'status' => true,
            'message' => (string) ($json['message'] ?? 'Verification successful'),
            'data' => is_array($json['data'] ?? null) ? $json['data'] : $json,
        ];
    }

    private function resolveRobostEndpoint(string $configured, string $path): string
    {
        $configured = trim($configured);
        if ($configured === '') {
            return 'https://robosttech.com/api/' . $path;
        }

        $trimmed = rtrim($configured, '/');
        $knownPaths = [
            'nin_verify', 'nin_phone', 'nin_demo', 'validation', 
            'validation_status', 'clearance', 'clearance_status'
        ];
        foreach ($knownPaths as $knownPath) {
            if (str_ends_with(strtolower($trimmed), '/' . $knownPath) || strtolower($trimmed) === 'https://robosttech.com/api/' . $knownPath) {
                return preg_replace('#/' . preg_quote($knownPath, '#') . '$#i', '/' . $path, $trimmed) ?? ('https://robosttech.com/api/' . $path);
            }
        }

        return $trimmed . '/' . $path;
    }


    public function verifyBvn(Request $request)
    {
        $request->validate([
            'number' => ['required', 'string'],
            'firstname' => ['nullable', 'string'],
            'lastname' => ['nullable', 'string'],
            'dob' => ['nullable', 'string'],
            'type' => ['required', 'in:basic,premium'],
            'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
        ]);

        $user = $request->user();
        $provider = $request->filled('api_provider_id')
            ? CustomApi::find($request->api_provider_id)
            : CustomApi::where('service_type', 'bvn_verification')->where('status', true)->first();

        if (!$provider || !$provider->status) {
            return response()->json(['status' => false, 'message' => 'No active provider for BVN verification.'], 503);
        }

        $providerContext = [
            'provider_source' => 'custom_api',
            'provider_id' => $provider->id,
            'provider_name' => $provider->name,
            'provider_identifier' => $provider->provider_identifier,
            'provider_endpoint' => $provider->endpoint,
            'service_type' => 'bvn_verification',
            'mode' => $request->type,
            'user' => $user->id,
        ];
        Log::info('API BVN verification provider resolved', $providerContext);

        $mode = $request->type;
        $settingKey = $mode === 'premium' ? 'developer_api_bvn_premium_price' : 'developer_api_bvn_basic_price';
        $fallback = (float) (($provider->price ?? null) ?: ($mode === 'premium'
            ? \App\Models\SystemSetting::get('bvn_premium_price', 500.0)
            : \App\Models\SystemSetting::get('bvn_basic_price', 100.0)));
        $price = (float) $user->getDeveloperApiPrice($settingKey, $fallback);

        $endpoint = (string) $provider->endpoint;
        $url = rtrim($endpoint, '/') . '/' . $request->number . '?type=' . $request->type;
        $headers = is_array($provider->headers) ? $provider->headers : [];

        $paid = app(PaidActionService::class)->run($user, $price, 'API: BVN Verification', 'BVNAPI', function () use ($headers, $url, $request, $provider, $user) {
            try {
                if (DataVerifyClient::isDataVerifyProvider($provider)) {
                    $client = new DataVerifyClient($provider);
                    $result = $client->verifyBvn((string) $request->number, (string) $request->type);
                    if ($result['ok']) {
                        $data = $result['data'] ?? [];
                        $stored = app(VerificationResultService::class)->create(
                            $user,
                            'bvn_verification',
                            (string) $request->number,
                            (string) $provider->name,
                            $data,
                            'success'
                        );

                        return [
                            'status' => true,
                            'message' => $result['message'] ?? 'BVN verified',
                            'result_id' => $stored->id,
                            'reference_id' => $stored->reference_id,
                            'data' => $data,
                        ];
                    }

                    $message = (string) ($result['message'] ?? 'Verification failed.');
                    app(VerificationResultService::class)->create(
                        $user,
                        'bvn_verification',
                        (string) $request->number,
                        (string) $provider->name,
                        ['error' => $message],
                        'failed'
                    );
                    throw new \RuntimeException($message);
                }

                $http = Http::timeout(60);
                if (!empty($headers)) {
                    $http = $http->withHeaders($headers);
                }

                $response = $http->post($url, [
                    'firstname' => $request->firstname,
                    'lastname' => $request->lastname,
                    'dob' => $request->dob,
                ]);

                if ($response->successful() && ($response['status'] ?? null) === 'success') {
                    $data = $response->json()['data'] ?? $response->json();
                    $result = app(VerificationResultService::class)->create(
                        $user,
                        'bvn_verification',
                        (string) $request->number,
                        (string) $provider->name,
                        $data,
                        'success'
                    );
                    return [
                        'status' => true,
                        'message' => 'BVN verified',
                        'result_id' => $result->id,
                        'reference_id' => $result->reference_id,
                        'data' => $data,
                    ];
                }

                $message = (string) ($response['message'] ?? 'Verification failed.');
                app(VerificationResultService::class)->create(
                    $user,
                    'bvn_verification',
                    (string) $request->number,
                    (string) $provider->name,
                    ['error' => $message],
                    'failed'
                );

                throw new \RuntimeException($message);
            } catch (\Exception $e) {
                app(VerificationResultService::class)->create(
                    $user,
                    'bvn_verification',
                    (string) $request->number,
                    (string) $provider->name,
                    ['error' => $e->getMessage()],
                    'failed'
                );
                throw $e;
            }
        });

        if (!$paid['ok']) {
            $code = isset($paid['txId']) ? 502 : 402;
            Log::error('API BVN Verification Failed: ' . $paid['message'], array_merge($providerContext, [
                'number' => (string) $request->number,
            ]));
            return response()->json(['status' => false, 'message' => $paid['message']], $code);
        }

        return response()->json($paid['result']);
    }

    public function getResult(Request $request, int $id)
    {
        $user = $request->user();
        $result = VerificationResult::findOrFail($id);
        if ($result->user_id !== $user->id) {
            return response()->json(['status' => false, 'message' => 'Forbidden'], 403);
        }

        // Log the access to the verification result
        activity()
            ->performedOn($result)
            ->causedBy($user)
            ->withProperty('ip', $request->ip())
            ->log('viewed_verification_result');

        return response()->json([
            'status' => true,
            'data' => $result,
        ]);
    }

    public function verifyClearance(Request $request)
    {
        $request->validate([
            'tracking_id' => ['nullable', 'string', 'max:120'],
            'number' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:255'],
            'mode' => ['nullable', 'in:submit,status'],
        ]);

        $trackingId = trim((string) ($request->tracking_id ?: $request->number));
        if ($trackingId === '') {
            return response()->json(['status' => false, 'message' => 'The tracking_id or number field is required.'], 422);
        }

        $user = $request->user();
        $mode = $request->input('mode', 'submit');
        $category = trim((string) $request->input('category', 'Improcessing Error'));
        $ipeClearanceMode = \App\Models\SystemSetting::get('ipe_clearance_mode', 'dataverify');

        if ($mode === 'status') {
            $existing = VerificationResult::where(function ($query) use ($trackingId) {
                $query->where('identifier', $trackingId)
                      ->orWhere('reference_id', $trackingId)
                      ->orWhere('response_data->tracking_id', $trackingId)
                      ->orWhere('response_data->new_tracking_id', $trackingId);
            })
            ->where('service_type', 'ipe_clearance')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereNull('user_id');
            })
            ->latest()
            ->first();

            if (!$existing) {
                return response()->json([
                    'status' => false,
                    'message' => 'No clearance application found for this tracking ID.',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Clearance record retrieved.',
                'result_id' => $existing->id,
                'reference_id' => $existing->reference_id,
                'data' => [
                    'tracking_id' => $existing->identifier,
                    'reference_id' => $existing->reference_id,
                    'status' => $existing->status,
                    'category' => data_get($existing->response_data, 'category', 'Improcessing Error'),
                    'admin_note' => $existing->admin_note,
                    'details' => $existing->response_data,
                ],
            ]);
        }

        // Mode: submit
        $price = (float) $user->getDeveloperApiPrice('developer_api_ipe_clearance_price', 700.0);

        $lockKey = "api_ipe_lock_{$user->id}_" . md5($trackingId);
        $lock = Cache::lock($lockKey, 20);
        if (!$lock->get()) {
            return response()->json(['status' => false, 'message' => 'A clearance request for this tracking ID is already in progress. Please wait.'], 429);
        }

        try {
            $paid = app(PaidActionService::class)->run($user, $price, 'API: IPE Clearance (' . $trackingId . ')', 'CLEARAPI', function () use ($trackingId, $category, $user, $price, $ipeClearanceMode) {
                $referenceId = 'CLR-' . strtoupper(bin2hex(random_bytes(4)));

                if ($ipeClearanceMode === 'dataverify') {
                    $dvProvider = CustomApi::query()->whereIn('service_type', ['ipe_clearance', 'clearance'])->where('status', true)->first();
                    $dvClient = new DataVerifyClient($dvProvider);
                    $dvRes = $dvClient->submitIpeClearance($trackingId);

                    if ($dvRes['ok'] ?? false) {
                        $apiData = $dvRes['data'] ?? [];
                        $newTid = VerificationResult::extractNewTrackingId($apiData, $trackingId);
                        $isSuccess = ($dvRes['status'] ?? '') === 'successful' || !empty($newTid);
                        $initialStatus = $isSuccess ? 'successful' : 'waiting_for_review';

                        $payload = [
                            'tracking_id' => $trackingId,
                            'category' => $category,
                            'new_tracking_id' => $newTid,
                            'amount_paid' => (float) $price,
                            'api_response' => $apiData,
                        ];

                        $vr = VerificationResult::create([
                            'user_id' => $user->id,
                            'service_type' => 'ipe_clearance',
                            'identifier' => $trackingId,
                            'provider_name' => 'DataVerify',
                            'status' => $initialStatus,
                            'reference_id' => $referenceId,
                            'admin_note' => $dvRes['message'] ?? 'Submitted to DataVerify.',
                            'response_data' => $payload,
                        ]);

                        return [
                            'status' => true,
                            'message' => 'Clearance request submitted successfully.',
                            'result_id' => $vr->id,
                            'reference_id' => $referenceId,
                            'data' => [
                                'tracking_id' => $trackingId,
                                'new_tracking_id' => $newTid,
                                'status' => $initialStatus,
                                'category' => $category,
                            ],
                        ];
                    }

                    throw new \RuntimeException($dvRes['message'] ?: 'DataVerify clearance submission failed.');
                }

                $payload = [
                    'tracking_id' => $trackingId,
                    'category' => $category,
                    'amount_paid' => (float) $price,
                ];

                $vr = VerificationResult::create([
                    'user_id' => $user->id,
                    'service_type' => 'ipe_clearance',
                    'identifier' => $trackingId,
                    'provider_name' => 'ADMIN_MANUAL',
                    'status' => 'waiting_for_review',
                    'reference_id' => $referenceId,
                    'admin_note' => 'Queued for review.',
                    'response_data' => $payload,
                ]);

                return [
                    'status' => true,
                    'message' => 'Clearance request submitted and queued for review.',
                    'result_id' => $vr->id,
                    'reference_id' => $referenceId,
                    'data' => [
                        'tracking_id' => $trackingId,
                        'status' => 'waiting_for_review',
                        'category' => $category,
                    ],
                ];
            });

            if (!$paid['ok']) {
                $code = isset($paid['txId']) ? 502 : 402;
                return response()->json(['status' => false, 'message' => $paid['message']], $code);
            }

            return response()->json($paid['result']);
        } finally {
            optional($lock)->release();
        }
    }

    public function verifyValidation(Request $request)
    {
        $request->validate([
            'number' => ['nullable', 'string', 'max:120'],
            'nin' => ['nullable', 'string', 'max:120'],
            'validation_reason' => ['nullable', 'string', 'max:255'],
            'mode' => ['nullable', 'in:submit,status'],
        ]);

        $ninNumber = trim((string) ($request->number ?: $request->nin));
        if ($ninNumber === '') {
            return response()->json(['status' => false, 'message' => 'The number or nin field is required.'], 422);
        }

        $user = $request->user();
        $mode = $request->input('mode', 'submit');
        $reason = trim((string) $request->input('validation_reason', 'NIN Validation'));
        $validationMode = \App\Models\SystemSetting::get('nin_validation_mode', 'auto');

        if ($mode === 'status') {
            $existing = VerificationResult::where(function ($query) use ($ninNumber) {
                $query->where('identifier', $ninNumber)
                      ->orWhere('reference_id', $ninNumber)
                      ->orWhere('response_data->nin', $ninNumber);
            })
            ->whereIn('service_type', ['validation', 'nin_validation'])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereNull('user_id');
            })
            ->latest()
            ->first();

            if (!$existing) {
                return response()->json([
                    'status' => false,
                    'message' => 'No validation application found for this NIN.',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Validation record retrieved.',
                'result_id' => $existing->id,
                'reference_id' => $existing->reference_id,
                'data' => [
                    'nin' => $existing->identifier,
                    'reference_id' => $existing->reference_id,
                    'status' => $existing->status,
                    'admin_note' => $existing->admin_note,
                    'details' => $existing->response_data,
                ],
            ]);
        }

        // Mode: submit
        $price = (float) $user->getDeveloperApiPrice('developer_api_nin_validation_price', 500.0);

        $lockKey = "api_val_lock_{$user->id}_" . md5($ninNumber);
        $lock = Cache::lock($lockKey, 20);
        if (!$lock->get()) {
            return response()->json(['status' => false, 'message' => 'A validation request for this NIN is already in progress. Please wait.'], 429);
        }

        try {
            $paid = app(PaidActionService::class)->run($user, $price, 'API: NIN Validation (' . $ninNumber . ')', 'VALAPI', function () use ($ninNumber, $reason, $user, $price, $validationMode) {
                $referenceId = 'VAL-' . strtoupper(bin2hex(random_bytes(4)));

                $dvProvider = CustomApi::query()->whereIn('service_type', ['validation', 'nin_validation', 'nin_verification'])->where('status', true)->where(function ($q) {
                    $q->where('provider_identifier', 'dataverify')->orWhere('endpoint', 'like', '%dataverify%');
                })->first();

                if ($dvProvider) {
                    $client = new DataVerifyClient($dvProvider);
                    $subRes = $client->submitNinValidation($ninNumber, 'no_record_found');
                    if ($subRes['ok'] ?? false) {
                        $vr = VerificationResult::create([
                            'user_id' => $user->id,
                            'service_type' => 'nin_validation',
                            'identifier' => $ninNumber,
                            'provider_name' => 'DataVerify',
                            'status' => 'waiting_for_review',
                            'reference_id' => $referenceId,
                            'admin_note' => $subRes['message'] ?: 'Validation submitted to provider.',
                            'response_data' => [
                                'nin' => $ninNumber,
                                'validation_reason' => $reason,
                                'amount_paid' => (float) $price,
                                'api_response' => $subRes['data'] ?? [],
                            ],
                        ]);

                        return [
                            'status' => true,
                            'message' => 'Validation request submitted successfully.',
                            'result_id' => $vr->id,
                            'reference_id' => $referenceId,
                            'data' => [
                                'nin' => $ninNumber,
                                'status' => 'waiting_for_review',
                                'validation_reason' => $reason,
                            ],
                        ];
                    }
                }

                $vr = VerificationResult::create([
                    'user_id' => $user->id,
                    'service_type' => 'nin_validation',
                    'identifier' => $ninNumber,
                    'provider_name' => 'ADMIN_MANUAL',
                    'status' => 'waiting_for_review',
                    'reference_id' => $referenceId,
                    'admin_note' => 'Validation request received and queued for review.',
                    'response_data' => [
                        'nin' => $ninNumber,
                        'validation_reason' => $reason,
                        'amount_paid' => (float) $price,
                    ],
                ]);

                return [
                    'status' => true,
                    'message' => 'Validation request queued for review.',
                    'result_id' => $vr->id,
                    'reference_id' => $referenceId,
                    'data' => [
                        'nin' => $ninNumber,
                        'status' => 'waiting_for_review',
                        'validation_reason' => $reason,
                    ],
                ];
            });

            if (!$paid['ok']) {
                $code = isset($paid['txId']) ? 502 : 402;
                return response()->json(['status' => false, 'message' => $paid['message']], $code);
            }

            return response()->json($paid['result']);
        } finally {
            optional($lock)->release();
        }
    }

    private function isTerminalIdentityError(string $message): bool
    {
        $lower = strtolower($message);
        $patterns = [
            'invalid nin', 'nin not found', 'record not found', 'no record found',
            'record does not exist', 'identity not found', 'data not found',
            'user not found', 'not found in database', 'invalid bvn', 'bvn not found',
            'invalid phone', 'phone not found', 'unregistered', 'does not exist'
        ];
        foreach ($patterns as $p) {
            if (str_contains($lower, $p)) {
                return true;
            }
        }
        return false;
    }
}
