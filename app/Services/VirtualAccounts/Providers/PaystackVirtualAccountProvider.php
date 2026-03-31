<?php

namespace App\Services\VirtualAccounts\Providers;

use App\Models\User;
use App\Models\VirtualAccount;
use App\Services\VirtualAccounts\Dto\VirtualAccountCreationResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaystackVirtualAccountProvider extends AbstractHttpProvider
{
    public function name(): string
    {
        return 'paystack';
    }

    public function supportsVirtualAccounts(): bool
    {
        $cfg = $this->getGatewayConfig('paystack');
        $apiCenter = $cfg['apiCenter'];
        $secret = $apiCenter?->paystack_secret_key ?: ($cfg['config']['secret_key'] ?? null);
        return (bool) $secret;
    }

    public function create(User $user): VirtualAccountCreationResult
    {
        $cfg = $this->getGatewayConfig('paystack');
        $apiCenter = $cfg['apiCenter'];
        $secret = $apiCenter?->paystack_secret_key ?: ($cfg['config']['secret_key'] ?? null);
        if (!$secret) {
            return new VirtualAccountCreationResult(false, gateway: 'paystack', message: 'Paystack is not configured');
        }

        $first = null;
        $last = null;
        if (!empty($user->fullname)) {
            $parts = preg_split('/\s+/', trim((string) $user->fullname));
            $first = $parts[0] ?? null;
            $last = $parts[1] ?? null;
        }

        try {
            $customerRes = Http::timeout(45)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $secret,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.paystack.co/customer', array_filter([
                    'email' => $user->email,
                    'first_name' => $first,
                    'last_name' => $last,
                    'phone' => $user->number ?? null,
                ], fn ($v) => $v !== null && $v !== ''));

            $customerJson = $customerRes->json();
            if (!$customerRes->successful()) {
                Log::warning('Paystack customer create failed', ['status' => $customerRes->status(), 'body' => $customerRes->body()]);
                return new VirtualAccountCreationResult(false, gateway: 'paystack', message: 'Unable to create Paystack customer');
            }

            $customerCode = $customerJson['data']['customer_code'] ?? null;
            if (!$customerCode) {
                return new VirtualAccountCreationResult(false, gateway: 'paystack', message: 'Paystack customer code missing');
            }

            $preferredBank = $cfg['config']['preferred_bank_slug'] ?? 'wema-bank';

            $ref = 'FUWA-VA-' . Str::upper(Str::random(10));
            $dedicatedRes = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $secret,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.paystack.co/dedicated_account', [
                    'customer' => $customerCode,
                    'preferred_bank' => $preferredBank,
                ]);

            $dedicatedJson = $dedicatedRes->json();
            if (!$dedicatedRes->successful()) {
                Log::warning('Paystack dedicated account create failed', ['status' => $dedicatedRes->status(), 'body' => $dedicatedRes->body()]);
                return new VirtualAccountCreationResult(false, gateway: 'paystack', message: 'Unable to create Paystack virtual account');
            }

            $data = $dedicatedJson['data'] ?? [];
            $accountNumber = $data['account_number'] ?? $data['accountNumber'] ?? null;
            $bankName = $data['bank']['name'] ?? $data['bank']['bank_name'] ?? $data['bank_name'] ?? null;
            $accountName = $data['account_name'] ?? $data['accountName'] ?? null;
            $accountId = $data['id'] ?? null;

            if (!$accountNumber) {
                return new VirtualAccountCreationResult(false, gateway: 'paystack', message: 'Paystack virtual account missing account number');
            }

            return new VirtualAccountCreationResult(
                true,
                gateway: 'paystack',
                accountNumber: (string) $accountNumber,
                bankName: $bankName ? (string) $bankName : null,
                accountName: $accountName ? (string) $accountName : null,
                status: 'active',
                reference: $ref,
                providerCustomerReference: (string) $customerCode,
                providerAccountReference: $accountId ? (string) $accountId : null,
                meta: [
                    'preferred_bank' => $preferredBank,
                ],
            );
        } catch (\Throwable $e) {
            Log::error('Paystack virtual account exception', ['error' => $e->getMessage()]);
            return new VirtualAccountCreationResult(false, gateway: 'paystack', message: 'Paystack virtual account is currently unavailable');
        }
    }

    public function syncStatus(VirtualAccount $virtualAccount): ?VirtualAccountCreationResult
    {
        return null;
    }
}

