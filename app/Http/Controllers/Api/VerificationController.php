<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomApi;
use App\Models\VerificationResult;
use App\Services\PaidActionService;
use App\Services\Vuvaa\VuvaaClient;
use App\Services\VerificationResultService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
        $provider = $request->filled('api_provider_id')
            ? CustomApi::find($request->api_provider_id)
            : $this->pickProviderForMode($request->mode);

        if (!$provider || !$provider->status) {
            return response()->json(['status' => false, 'message' => 'No active provider for NIN verification.'], 503);
        }

        $price = (float) ($provider->price ?? \App\Models\SystemSetting::get('nin_verification_price', 200));
        $endpoint = (string) $provider->endpoint;
        $headers = is_array($provider->headers) ? $provider->headers : [];

        $paid = app(PaidActionService::class)->run($user, $price, 'API: NIN Verification', 'NINAPI', function () use ($headers, $endpoint, $request, $provider, $user) {
            try {
                if (VuvaaClient::isVuvaaProvider($provider)) {
                    if ($request->mode !== 'nin') {
                        throw new \RuntimeException('Selected provider does not support this verification mode.');
                    }

                    $client = new VuvaaClient($provider);
                    $result = $client->verifyNin((string) $request->number);
                    if (!$result['ok']) {
                        throw new \RuntimeException((string) ($result['message'] ?? 'Verification failed.'));
                    }

                    $data = $result['data'];
                    $vr = app(VerificationResultService::class)->create(
                        $user,
                        'nin_verification',
                        (string) $request->number,
                        (string) $provider->name,
                        $data,
                        'success'
                    );

                    return [
                        'status' => true,
                        'message' => 'NIN verified',
                        'result_id' => $vr->id,
                        'reference_id' => $vr->reference_id,
                        'data' => $data,
                    ];
                }

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

                if ($response->successful() && ($response['status'] ?? null) === 'success') {
                    $data = $response->json()['data'] ?? $response->json();
                    $vr = app(VerificationResultService::class)->create(
                        $user,
                        'nin_verification',
                        (string) $request->number,
                        (string) $provider->name,
                        $data,
                        'success'
                    );
                    return [
                        'status' => true,
                        'message' => 'NIN verified',
                        'result_id' => $vr->id,
                        'reference_id' => $vr->reference_id,
                        'data' => $data,
                    ];
                }

                $message = (string) ($response['message'] ?? 'Verification failed.');
                app(VerificationResultService::class)->create(
                    $user,
                    'nin_verification',
                    (string) $request->number,
                    (string) $provider->name,
                    ['error' => $message],
                    'failed'
                );

                throw new \RuntimeException($message);
            } catch (\Exception $e) {
                app(VerificationResultService::class)->create(
                    $user,
                    'nin_verification',
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
            return response()->json(['status' => false, 'message' => $paid['message']], $code);
        }

        return response()->json($paid['result']);
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

    public function verifyBvn(Request $request)
    {
        $request->validate([
            'number' => ['required', 'string'],
            'firstname' => ['required', 'string'],
            'lastname' => ['required', 'string'],
            'dob' => ['required', 'string'],
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

        $mode = $request->type;
        $price = (float) (($provider->price ?? null) ?: ($mode === 'premium'
            ? \App\Models\SystemSetting::get('bvn_premium_price', 500)
            : \App\Models\SystemSetting::get('bvn_basic_price', 100)));

        $endpoint = (string) $provider->endpoint;
        $url = rtrim($endpoint, '/') . '/' . $request->number . '?type=' . $request->type;
        $headers = is_array($provider->headers) ? $provider->headers : [];

        $paid = app(PaidActionService::class)->run($user, $price, 'API: BVN Verification', 'BVNAPI', function () use ($headers, $url, $request, $provider, $user) {
            try {
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
