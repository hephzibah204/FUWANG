<?php

namespace App\Services\VirtualAccounts\Providers;

use App\Models\User;
use App\Models\VirtualAccount;
use App\Services\VirtualAccounts\Dto\VirtualAccountCreationResult;
use App\Support\PaymentApiUserMessage;
use App\Support\PaymentProviderCredentials;
use App\Support\UserKycIdentifiers;
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
        $f = PaymentProviderCredentials::flutterwave($this->getGatewayConfig('flutterwave')['apiCenter']);

        return (bool) ($f['secret_key'] ?? null);
    }

    public function create(User $user): VirtualAccountCreationResult
    {
        $secret = PaymentProviderCredentials::flutterwave($this->getGatewayConfig('flutterwave')['apiCenter'])['secret_key'];
        if (! $secret) {
            return new VirtualAccountCreationResult(false, gateway: 'flutterwave', message: 'Flutterwave is not configured');
        }

        $identity = UserKycIdentifiers::preferredPaymentIdentity($user);
        if ($identity === null) {
            return new VirtualAccountCreationResult(
                false,
                gateway: 'flutterwave',
                message: 'Flutterwave requires a verified BVN or NIN for a permanent account. Complete identity verification (BVN or NIN), then try Load Accounts again.'
            );
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

        $txRef = 'FUWA-VA-'.Str::uuid()->toString();

        $payload = array_filter([
            'currency' => 'NGN',
            'email' => $user->email,
            'tx_ref' => $txRef,
            'is_permanent' => true,
            'phonenumber' => $user->number ?? null,
            'firstname' => $firstName,
            'lastname' => $lastName,
            'narration' => 'Fuwa Wallet Funding',
        ], fn ($v) => $v !== null && $v !== '');
        $payload[$identity['type']] = $identity['value'];

        try {
            $res = Http::timeout(60)
                ->acceptJson()
                ->asJson()
                ->withHeaders([
                    'Authorization' => 'Bearer '.$secret,
                ])
                ->post('https://api.flutterwave.com/v3/virtual-account-numbers', $payload);

            $json = $res->json();
            $ok = $res->successful() && is_array($json) && (($json['status'] ?? '') === 'success');
            if (! $ok) {
                Log::warning('Flutterwave virtual account create failed', ['status' => $res->status(), 'body' => $res->body()]);
                $msg = PaymentApiUserMessage::flutterwave(is_array($json) ? $json : null, 'Unable to create Flutterwave virtual account');

                return new VirtualAccountCreationResult(false, gateway: 'flutterwave', message: $msg);
            }

            $data = $json['data'] ?? [];
            $accountNumber = $data['account_number'] ?? null;
            $bankName = $data['bank_name'] ?? null;
            $accountName = $data['account_name'] ?? $data['accountName'] ?? null;
            $responseCode = (string) ($data['response_code'] ?? $data['responseCode'] ?? '');
            $orderRef = $data['order_ref'] ?? $data['orderRef'] ?? null;

            if (! $accountNumber) {
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
