<?php

namespace App\Services\VirtualAccounts\Providers;

use App\Models\User;
use App\Models\VirtualAccount;
use App\Services\VirtualAccounts\Dto\VirtualAccountCreationResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FlutterwaveVirtualAccountProvider extends AbstractHttpProvider
{
    public function name(): string
    {
        return 'flutterwave';
    }

    public function supportsVirtualAccounts(): bool
    {
        $cfg = $this->getGatewayConfig('flutterwave');
        $apiCenter = $cfg['apiCenter'];
        $secret = $apiCenter?->flutterwave_secret_key ?: ($cfg['config']['secret_key'] ?? null);
        return (bool) $secret;
    }

    public function create(User $user): VirtualAccountCreationResult
    {
        $cfg = $this->getGatewayConfig('flutterwave');
        $apiCenter = $cfg['apiCenter'];
        $secret = $apiCenter?->flutterwave_secret_key ?: ($cfg['config']['secret_key'] ?? null);
        if (!$secret) {
            return new VirtualAccountCreationResult(false, gateway: 'flutterwave', message: 'Flutterwave is not configured');
        }

        $first = null;
        $last = null;
        if (!empty($user->fullname)) {
            $parts = preg_split('/\s+/', trim((string) $user->fullname));
            $first = $parts[0] ?? null;
            $last = $parts[1] ?? null;
        }

        $txRef = 'FUWA-VA-' . Str::uuid()->toString();

        $payload = array_filter([
            'currency' => 'NGN',
            'email' => $user->email,
            'tx_ref' => $txRef,
            'is_permanent' => true,
            'phonenumber' => $user->number ?? null,
            'firstname' => $first,
            'lastname' => $last,
            'narration' => 'Fuwa Wallet Funding',
        ], fn ($v) => $v !== null && $v !== '');

        try {
            $res = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $secret,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.flutterwave.com/v3/virtual-account-numbers', $payload);

            $json = $res->json();
            if (!$res->successful()) {
                Log::warning('Flutterwave virtual account create failed', ['status' => $res->status(), 'body' => $res->body()]);
                return new VirtualAccountCreationResult(false, gateway: 'flutterwave', message: 'Unable to create Flutterwave virtual account');
            }

            $data = $json['data'] ?? [];
            $accountNumber = $data['account_number'] ?? null;
            $bankName = $data['bank_name'] ?? null;
            $accountName = $data['account_name'] ?? $data['accountName'] ?? null;
            $responseCode = (string) ($data['response_code'] ?? $data['responseCode'] ?? '');
            $orderRef = $data['order_ref'] ?? $data['orderRef'] ?? null;

            if (!$accountNumber) {
                return new VirtualAccountCreationResult(false, gateway: 'flutterwave', message: 'Flutterwave returned no account number');
            }

            $status = 'active';

            return new VirtualAccountCreationResult(
                true,
                gateway: 'flutterwave',
                accountNumber: (string) $accountNumber,
                bankName: $bankName ? (string) $bankName : null,
                accountName: $accountName ? (string) $accountName : null,
                status: $status,
                reference: $txRef,
                providerAccountReference: $orderRef ? (string) $orderRef : null,
                meta: [
                    'response_code' => $responseCode,
                    'response_message' => $data['response_message'] ?? null,
                ],
            );
        } catch (\Throwable $e) {
            Log::error('Flutterwave virtual account exception', ['error' => $e->getMessage()]);
            return new VirtualAccountCreationResult(false, gateway: 'flutterwave', message: 'Flutterwave virtual account is currently unavailable');
        }
    }

    public function syncStatus(VirtualAccount $virtualAccount): ?VirtualAccountCreationResult
    {
        return null;
    }
}
