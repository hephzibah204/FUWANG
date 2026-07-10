<?php

namespace App\Services\VirtualAccounts\Providers;

use App\Models\User;
use App\Models\VirtualAccount;
use App\Services\VirtualAccounts\Dto\VirtualAccountCreationResult;
use App\Support\PaymentProviderCredentials;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayvesselVirtualAccountProvider extends AbstractHttpProvider
{
    public function name(): string
    {
        return 'payvessel';
    }

    public function supportsVirtualAccounts(): bool
    {
        $p = PaymentProviderCredentials::payvessel($this->getGatewayConfig('payvessel')['apiCenter']);

        return (bool) ($p['endpoint'] && $p['api_key']);
    }

    public function create(User $user): VirtualAccountCreationResult
    {
        $p = PaymentProviderCredentials::payvessel($this->getGatewayConfig('payvessel')['apiCenter']);
        $endpoint = $p['endpoint'];
        $apiKey = $p['api_key'];
        $businessId = $p['business_id'];

        if (!$endpoint || !$apiKey) {
            return new VirtualAccountCreationResult(false, gateway: 'payvessel', message: 'PayVessel is not configured');
        }

        $payload = [
            'email' => $user->email,
            'name' => $user->fullname ?? $user->username ?? $user->email,
            'phoneNumber' => $user->number ?? null,
        ];
        if ($businessId) {
            $payload['businessId'] = $businessId;
        }

        try {
            $res = Http::timeout(60)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'api-key' => $apiKey,
                    'Accept' => 'application/json',
                ])
                ->post($endpoint, $payload);

            $json = $res->json();
            if (!$res->successful()) {
                Log::warning('PayVessel reserve account failed', ['status' => $res->status(), 'body' => $res->body()]);
                return new VirtualAccountCreationResult(false, gateway: 'payvessel', message: 'PayVessel gateway error');
            }

            $acct = $json['data']['accountNumber']
                ?? $json['data']['account_number']
                ?? $json['accountNumber']
                ?? $json['account_number']
                ?? $json['bankAccounts'][0]['accountNumber']
                ?? null;

            $acctName = $json['data']['accountName']
                ?? $json['data']['account_name']
                ?? $json['accountName']
                ?? $json['account_name']
                ?? null;

            if (!$acct) {
                return new VirtualAccountCreationResult(false, gateway: 'payvessel', message: $json['message'] ?? 'PayVessel response not recognized');
            }

            return new VirtualAccountCreationResult(
                true,
                gateway: 'payvessel',
                accountNumber: (string) $acct,
                bankName: '9PSB / PayVessel',
                accountName: $acctName ? (string) $acctName : null,
                status: 'active',
                meta: [
                    'business_id' => $businessId,
                ],
            );
        } catch (\Throwable $e) {
            Log::error('PayVessel virtual account exception', ['error' => $e->getMessage()]);
            return new VirtualAccountCreationResult(false, gateway: 'payvessel', message: 'PayVessel unavailable');
        }
    }

    public function syncStatus(VirtualAccount $virtualAccount): ?VirtualAccountCreationResult
    {
        return null;
    }
}

