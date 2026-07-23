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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VerificationController extends Controller
{
    public function verifyNin(Request $request)
    {
        $request->validate([
            'number' => ['required', 'string'],
            'firstname' => ['required', 'string'],
            'lastname' => ['required', 'string'],
            'dob' => ['required', 'string'],
            'mode' => ['required', 'in:nin,phone'],
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
                ->where('service_type', 'nin_verification')
                ->where('status', true)
                ->orderBy('priority', 'asc')
                ->get();
        }

        if (count($activeProviders) === 0) {
            return response()->json(['status' => false, 'message' => 'No active provider for NIN verification.'], 503);
        }

        // We use the first provider's price as the baseline, or a system setting
        $firstProvider = $activeProviders[0];
        $price = (float) \App\Models\SystemSetting::get(
            'developer_api_nin_price',
            100.0
        );

        $paid = app(PaidActionService::class)->run($user, $price, 'API: NIN Verification', 'NINAPI', function () use ($activeProviders, $request, $user) {
            $errors = [];
            foreach ($activeProviders as $provider) {
                try {
                    $result = $this->tryProvider($provider, $request, $user);
                    if ($result['status']) {
                        return $result;
                    }
                    $errors[] = $provider->name . ': ' . ($result['message'] ?? 'Unknown error');
                } catch (\Throwable $e) {
                    $errors[] = $provider->name . ': ' . $e->getMessage();
                    Log::warning("API Provider failover: {$provider->name} failed.", ['error' => $e->getMessage()]);
                }
            }

            throw new \RuntimeException('All verification providers failed: ' . implode(' | ', $errors));
        });

        if (!$paid['ok']) {
            $code = isset($paid['txId']) ? 502 : 402;
            return response()->json(['status' => false, 'message' => $paid['message']], $code);
        }

        return response()->json($paid['result']);
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
        $price = (float) \App\Models\SystemSetting::get(
            $mode === 'premium' ? 'developer_api_bvn_premium_price' : 'developer_api_bvn_basic_price',
            (float) (($provider->price ?? null) ?: ($mode === 'premium'
                ? \App\Models\SystemSetting::get('bvn_premium_price', 500)
                : \App\Models\SystemSetting::get('bvn_basic_price', 100)))
        );

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
}
