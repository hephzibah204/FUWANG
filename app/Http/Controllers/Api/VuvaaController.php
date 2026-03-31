<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomApi;
use App\Services\VerificationResultService;
use App\Services\Vuvaa\VuvaaClient;
use Illuminate\Http\Request;

class VuvaaController extends Controller
{
    public function createUser(Request $request)
    {
        $payload = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
            'firstname' => ['required', 'string'],
            'lastname' => ['required', 'string'],
            'username' => ['required', 'string'],
            'dob' => ['required', 'string'],
            'gender' => ['required', 'string'],
            'address' => ['required', 'string'],
            'state' => ['required', 'string'],
            'phone' => ['required', 'string'],
            'account_level' => ['required', 'string'],
            'enterprise_id' => ['required', 'string'],
            'ip_addresses' => ['required', 'array'],
            'ip_addresses.*' => ['string'],
            'ip_val_flag' => ['required', 'integer', 'in:0,1'],
            'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
        ]);

        $provider = $this->pickProvider($request);
        if (!$provider) {
            return response()->json(['status' => false, 'message' => 'No active VUVAA provider configured.'], 503);
        }

        $client = new VuvaaClient($provider);
        $result = $client->createUser($this->withoutKeys($payload, ['api_provider_id']));

        $this->store($request, 'vuvaa_create_user', (string) $payload['username'], $provider->name, $result['data'] ?? $result, $result['ok'] ? 'success' : 'failed');

        if (!$result['ok']) {
            return response()->json(['status' => false, 'message' => $result['message'] ?? 'Request failed.', 'data' => $result['data'] ?? null], 502);
        }

        return response()->json(['status' => true, 'message' => 'OK', 'data' => $result['data'] ?? null]);
    }

    public function login(Request $request)
    {
        $payload = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
        ]);

        $provider = $this->pickProvider($request);
        if (!$provider) {
            return response()->json(['status' => false, 'message' => 'No active VUVAA provider configured.'], 503);
        }

        $client = new VuvaaClient($provider);
        $result = $client->postEncrypted('/login', $this->withoutKeys($payload, ['api_provider_id']), false);

        $this->store($request, 'vuvaa_login', (string) $payload['username'], $provider->name, $result['data'] ?? $result, $result['ok'] ? 'success' : 'failed');

        if (!$result['ok']) {
            return response()->json(['status' => false, 'message' => $result['message'] ?? 'Login failed.', 'data' => $result['data'] ?? null], 502);
        }

        return response()->json(['status' => true, 'message' => 'OK', 'data' => $result['data'] ?? null]);
    }

    public function verifyNin(Request $request)
    {
        $payload = $request->validate([
            'nin' => ['required', 'string'],
            'reference_id' => ['nullable', 'string', 'max:120'],
            'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
        ]);

        $provider = $this->pickProvider($request);
        if (!$provider) {
            return response()->json(['status' => false, 'message' => 'No active VUVAA provider configured.'], 503);
        }

        $client = new VuvaaClient($provider);
        $result = $client->verifyNin((string) $payload['nin'], $payload['reference_id'] ?? null);

        $this->store($request, 'vuvaa_verify_nin', (string) $payload['nin'], $provider->name, $result['data'] ?? $result, $result['ok'] ? 'success' : 'failed');

        if (!$result['ok']) {
            return response()->json(['status' => false, 'message' => $result['message'] ?? 'Verification failed.', 'data' => $result['data'] ?? null], 502);
        }

        return response()->json(['status' => true, 'message' => 'OK', 'data' => $result['data'] ?? null]);
    }

    public function inPersonVerification(Request $request)
    {
        $payload = $request->validate([
            'nin' => ['required', 'string'],
            'image' => ['required', 'string'],
            'reference_id' => ['nullable', 'string', 'max:120'],
            'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
        ]);

        $provider = $this->pickProvider($request);
        if (!$provider) {
            return response()->json(['status' => false, 'message' => 'No active VUVAA provider configured.'], 503);
        }

        $client = new VuvaaClient($provider);
        $result = $client->verifyInPerson((string) $payload['nin'], (string) $payload['image'], $payload['reference_id'] ?? null);

        $this->store($request, 'vuvaa_in_person', (string) $payload['nin'], $provider->name, $result['data'] ?? $result, $result['ok'] ? 'success' : 'failed');

        if (!$result['ok']) {
            return response()->json(['status' => false, 'message' => $result['message'] ?? 'Verification failed.', 'data' => $result['data'] ?? null], 502);
        }

        return response()->json(['status' => true, 'message' => 'OK', 'data' => $result['data'] ?? null]);
    }

    public function shareCode(Request $request)
    {
        $payload = $request->validate([
            'share_code' => ['required', 'string', 'max:64'],
            'reference_id' => ['nullable', 'string', 'max:120'],
            'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
        ]);

        $provider = $this->pickProvider($request);
        if (!$provider) {
            return response()->json(['status' => false, 'message' => 'No active VUVAA provider configured.'], 503);
        }

        $client = new VuvaaClient($provider);
        $result = $client->verifyShareCode((string) $payload['share_code'], $payload['reference_id'] ?? null);

        $this->store($request, 'vuvaa_share_code', (string) $payload['share_code'], $provider->name, $result['data'] ?? $result, $result['ok'] ? 'success' : 'failed');

        if (!$result['ok']) {
            return response()->json(['status' => false, 'message' => $result['message'] ?? 'Verification failed.', 'data' => $result['data'] ?? null], 502);
        }

        return response()->json(['status' => true, 'message' => 'OK', 'data' => $result['data'] ?? null]);
    }

    public function requery(Request $request)
    {
        $payload = $request->validate([
            'reference_id' => ['required', 'string', 'max:120'],
            'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
        ]);

        $provider = $this->pickProvider($request);
        if (!$provider) {
            return response()->json(['status' => false, 'message' => 'No active VUVAA provider configured.'], 503);
        }

        $client = new VuvaaClient($provider);
        $result = $client->requery((string) $payload['reference_id']);

        $this->store($request, 'vuvaa_requery', (string) $payload['reference_id'], $provider->name, $result['data'] ?? $result, $result['ok'] ? 'success' : 'failed');

        if (!$result['ok']) {
            return response()->json(['status' => false, 'message' => $result['message'] ?? 'Requery failed.', 'data' => $result['data'] ?? null], 502);
        }

        return response()->json(['status' => true, 'message' => 'OK', 'data' => $result['data'] ?? null]);
    }

    public function wallet(Request $request)
    {
        $payload = $request->validate([
            'filters' => ['nullable', 'array'],
            'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
        ]);

        $provider = $this->pickProvider($request);
        if (!$provider) {
            return response()->json(['status' => false, 'message' => 'No active VUVAA provider configured.'], 503);
        }

        $client = new VuvaaClient($provider);
        $result = $client->getWalletDetails((array) ($payload['filters'] ?? []));

        $this->store($request, 'vuvaa_wallet', (string) $request->user()->id, $provider->name, $result['data'] ?? $result, $result['ok'] ? 'success' : 'failed');

        if (!$result['ok']) {
            return response()->json(['status' => false, 'message' => $result['message'] ?? 'Request failed.', 'data' => $result['data'] ?? null], 502);
        }

        return response()->json(['status' => true, 'message' => 'OK', 'data' => $result['data'] ?? null]);
    }

    public function transactionHistory(Request $request)
    {
        $payload = $request->validate([
            'filters' => ['nullable', 'array'],
            'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
        ]);

        $provider = $this->pickProvider($request);
        if (!$provider) {
            return response()->json(['status' => false, 'message' => 'No active VUVAA provider configured.'], 503);
        }

        $client = new VuvaaClient($provider);
        $result = $client->transactionHistory((array) ($payload['filters'] ?? []));

        $this->store($request, 'vuvaa_transaction_history', (string) $request->user()->id, $provider->name, $result['data'] ?? $result, $result['ok'] ? 'success' : 'failed');

        if (!$result['ok']) {
            return response()->json(['status' => false, 'message' => $result['message'] ?? 'Request failed.', 'data' => $result['data'] ?? null], 502);
        }

        return response()->json(['status' => true, 'message' => 'OK', 'data' => $result['data'] ?? null]);
    }

    public function reasons(Request $request)
    {
        $payload = $request->validate([
            'filters' => ['nullable', 'array'],
            'api_provider_id' => ['nullable', 'exists:custom_apis,id'],
        ]);

        $provider = $this->pickProvider($request);
        if (!$provider) {
            return response()->json(['status' => false, 'message' => 'No active VUVAA provider configured.'], 503);
        }

        $client = new VuvaaClient($provider);
        $result = $client->getNimcReasons((array) ($payload['filters'] ?? []));

        $this->store($request, 'vuvaa_reasons', (string) $request->user()->id, $provider->name, $result['data'] ?? $result, $result['ok'] ? 'success' : 'failed');

        if (!$result['ok']) {
            return response()->json(['status' => false, 'message' => $result['message'] ?? 'Request failed.', 'data' => $result['data'] ?? null], 502);
        }

        return response()->json(['status' => true, 'message' => 'OK', 'data' => $result['data'] ?? null]);
    }

    private function pickProvider(Request $request): ?CustomApi
    {
        if ($request->filled('api_provider_id')) {
            $p = CustomApi::find($request->integer('api_provider_id'));
            if ($p && $p->status && VuvaaClient::isVuvaaProvider($p)) {
                return $p;
            }
            return null;
        }

        return CustomApi::query()
            ->where('status', true)
            ->where(function ($q) {
                $q->where('provider_identifier', 'like', '%vuvaa%')
                    ->orWhere('endpoint', 'like', '%vuvaa.com%');
            })
            ->orderByRaw("CASE WHEN service_type = 'nin_verification' THEN 0 WHEN service_type = 'nin_face_verification' THEN 1 ELSE 2 END")
            ->orderBy('priority', 'asc')
            ->first();
    }

    private function store(Request $request, string $serviceType, string $identifier, string $providerName, mixed $data, string $status): void
    {
        app(VerificationResultService::class)->create(
            $request->user(),
            $serviceType,
            $identifier,
            $providerName,
            $data,
            $status,
            'VUVAA'
        );
    }

    private function withoutKeys(array $data, array $keys): array
    {
        foreach ($keys as $k) {
            unset($data[$k]);
        }
        return $data;
    }
}

