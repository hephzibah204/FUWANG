<?php

namespace App\Services\VirtualAccounts\Providers;

use App\Models\User;
use App\Models\VirtualAccount;
use App\Services\VirtualAccounts\Dto\VirtualAccountCreationResult;
use App\Support\PaymentApiUserMessage;
use App\Support\PaymentProviderCredentials;
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
        $secret = PaymentProviderCredentials::paystack($this->getGatewayConfig('paystack')['apiCenter'])['secret_key'];

        return (bool) $secret;
    }

    public function create(User $user): VirtualAccountCreationResult
    {
        $cfg = $this->getGatewayConfig('paystack');
        $secret = PaymentProviderCredentials::paystack($cfg['apiCenter'])['secret_key'];
        if (! $secret) {
            return new VirtualAccountCreationResult(false, gateway: 'paystack', message: 'Paystack is not configured');
        }

        $first = null;
        $last = null;
        if (! empty($user->fullname)) {
            $parts = preg_split('/\s+/', trim((string) $user->fullname));
            $first = $parts[0] ?? null;
            $last = $parts[1] ?? null;
        }
        $firstName = $first ?? $user->username ?? 'Customer';
        $lastName = $last ?? $first ?? $user->username ?? 'User';

        try {
            $resolved = $this->resolvePaystackCustomerCode($secret, $user, $firstName, $lastName);
            if (! $resolved['ok']) {
                return new VirtualAccountCreationResult(false, gateway: 'paystack', message: $resolved['message']);
            }
            $customerCode = $resolved['code'];

            $preferredBank = $cfg['config']['preferred_bank_slug'] ?? 'wema-bank';

            $ref = 'FUWA-VA-'.Str::upper(Str::random(10));
            $dedicatedRes = Http::timeout(60)
                ->acceptJson()
                ->asJson()
                ->withToken($secret)
                ->post('https://api.paystack.co/dedicated_account', [
                    'customer' => $customerCode,
                    'preferred_bank' => $preferredBank,
                ]);

            $dedicatedJson = $dedicatedRes->json();
            if (! $dedicatedRes->successful() || ! ($dedicatedJson['status'] ?? false)) {
                Log::warning('Paystack dedicated account create failed', ['status' => $dedicatedRes->status(), 'body' => $dedicatedRes->body()]);
                $msg = PaymentApiUserMessage::paystack(is_array($dedicatedJson) ? $dedicatedJson : null, 'Unable to create Paystack virtual account');

                return new VirtualAccountCreationResult(false, gateway: 'paystack', message: $msg);
            }

            $data = $dedicatedJson['data'] ?? [];
            $accountNumber = $data['account_number'] ?? $data['accountNumber'] ?? null;
            $bankName = $data['bank']['name'] ?? $data['bank']['bank_name'] ?? $data['bank_name'] ?? null;
            $accountName = $data['account_name'] ?? $data['accountName'] ?? null;
            $accountId = $data['id'] ?? null;

            if (! $accountNumber) {
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

    /**
     * Create or reuse a Paystack customer by email (handles duplicates).
     *
     * @return array{ok: true, code: string}|array{ok: false, message: string}
     */
    private function resolvePaystackCustomerCode(string $secret, User $user, string $firstName, string $lastName): array
    {
        $createPayload = array_filter([
            'email' => $user->email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $user->number ?? null,
        ], fn ($v) => $v !== null && $v !== '');

        $customerRes = Http::timeout(45)
            ->acceptJson()
            ->asJson()
            ->withToken($secret)
            ->post('https://api.paystack.co/customer', $createPayload);

        $customerJson = $customerRes->json();
        if ($customerRes->successful() && ($customerJson['status'] ?? false)) {
            $code = $customerJson['data']['customer_code'] ?? null;
            if ($code) {
                return ['ok' => true, 'code' => (string) $code];
            }
        }

        $listRes = Http::timeout(45)
            ->acceptJson()
            ->withToken($secret)
            ->get('https://api.paystack.co/customer', [
                'email' => $user->email,
                'perPage' => 5,
            ]);

        $listJson = $listRes->json();
        if ($listRes->successful() && ($listJson['status'] ?? false) && ! empty($listJson['data'])) {
            foreach ((array) $listJson['data'] as $row) {
                $code = $row['customer_code'] ?? null;
                if ($code) {
                    return ['ok' => true, 'code' => (string) $code];
                }
            }
        }

        Log::warning('Paystack customer resolve failed', [
            'create_status' => $customerRes->status(),
            'create_body' => $customerRes->body(),
            'list_status' => $listRes->status(),
            'list_body' => $listRes->body(),
        ]);

        $hintJson = is_array($customerJson) ? $customerJson : null;
        $msg = PaymentApiUserMessage::paystack($hintJson, 'Unable to create Paystack customer');
        if ($msg === 'Unable to create Paystack customer' && is_array($listJson)) {
            $msg = PaymentApiUserMessage::paystack($listJson, $msg);
        }

        return ['ok' => false, 'message' => $msg];
    }

    public function syncStatus(VirtualAccount $virtualAccount): ?VirtualAccountCreationResult
    {
        return null;
    }
}
