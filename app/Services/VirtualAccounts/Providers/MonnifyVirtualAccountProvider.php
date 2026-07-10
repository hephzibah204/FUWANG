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

class MonnifyVirtualAccountProvider extends AbstractHttpProvider
{
    public function name(): string
    {
        return 'monnify';
    }

    public function supportsVirtualAccounts(): bool
    {
        $m = PaymentProviderCredentials::monnify($this->getGatewayConfig('monnify')['apiCenter']);

        return (bool) ($m['api_key'] && $m['secret_key'] && $m['contract_code']);
    }

    public function create(User $user): VirtualAccountCreationResult
    {
        $cfg = $this->getGatewayConfig('monnify');
        $m = PaymentProviderCredentials::monnify($cfg['apiCenter']);
        $apiKey = $m['api_key'];
        $secretKey = $m['secret_key'];
        $contractCode = $m['contract_code'];
        $authEndpoint = $m['endpoint_auth'];
        $reserveEndpoint = $m['endpoint_reserve'];

        if (! $apiKey || ! $secretKey || ! $contractCode) {
            return new VirtualAccountCreationResult(false, gateway: 'monnify', message: 'Monnify is not configured');
        }

        $identity = UserKycIdentifiers::preferredPaymentIdentity($user);
        if ($identity === null) {
            return new VirtualAccountCreationResult(
                false,
                gateway: 'monnify',
                message: 'Monnify requires a verified BVN or NIN. Complete identity verification (BVN or NIN) and try Load Accounts again.'
            );
        }

        $accountReference = 'FUWA-'.strtoupper(bin2hex(random_bytes(6)));

        try {
            $basic = base64_encode($apiKey.':'.$secretKey);
            $authRes = Http::timeout(45)->acceptJson()->withHeaders([
                'Authorization' => 'Basic '.$basic,
            ])->post($authEndpoint);

            $authJson = $authRes->json();
            if (! $authRes->successful() || (isset($authJson['requestSuccessful']) && $authJson['requestSuccessful'] === false)) {
                Log::warning('Monnify auth failed', ['status' => $authRes->status(), 'body' => $authRes->body()]);
                $msg = PaymentApiUserMessage::monnify(is_array($authJson) ? $authJson : null, 'Monnify auth failed');

                return new VirtualAccountCreationResult(false, gateway: 'monnify', message: $msg);
            }

            $token = is_array($authJson)
                ? ($authJson['responseBody']['accessToken'] ?? $authJson['responseBody']['token'] ?? null)
                : null;
            if (! $token) {
                return new VirtualAccountCreationResult(false, gateway: 'monnify', message: 'Monnify token missing');
            }

            $payload = [
                'accountReference' => $accountReference,
                'accountName' => $user->fullname ?? $user->username ?? 'Fuwa Wallet',
                'currencyCode' => 'NGN',
                'contractCode' => $contractCode,
                'customerEmail' => $user->email,
                'customerName' => $user->fullname ?? $user->username ?? $user->email,
                'getAllAvailableBanks' => true, 
            ]; 
            $payload[$identity['type']] = $identity['value'];

            $reserveRes = Http::timeout(60)
                ->acceptJson()
                ->asJson()
                ->withToken($token)
                ->post($reserveEndpoint, $payload);

            $reserveJson = $reserveRes->json();
            if (! $reserveRes->successful() || (isset($reserveJson['requestSuccessful']) && $reserveJson['requestSuccessful'] === false)) {
                Log::warning('Monnify reserve failed', ['status' => $reserveRes->status(), 'body' => $reserveRes->body()]);
                $msg = PaymentApiUserMessage::monnify(is_array($reserveJson) ? $reserveJson : null, 'Monnify reserve failed');

                return new VirtualAccountCreationResult(false, gateway: 'monnify', message: $msg);
            }

            if (! is_array($reserveJson)) {
                return new VirtualAccountCreationResult(false, gateway: 'monnify', message: 'Monnify reserve failed');
            }
            $body = $reserveJson['responseBody'] ?? $reserveJson['data'] ?? $reserveJson;
            $accounts = $body['accounts'] ?? $body['bankAccounts'] ?? $body['reservedAccounts'] ?? [];
            $firstAccount = null;
            foreach ((array) $accounts as $acc) {
                $acct = (string) ($acc['accountNumber'] ?? $acc['account_number'] ?? '');
                if ($acct !== '') {
                    $firstAccount = $acc;
                    break;
                }
            }

            if (! $firstAccount) {
                return new VirtualAccountCreationResult(false, gateway: 'monnify', message: 'Monnify returned no bank accounts');
            }

            $bankName = (string) ($firstAccount['bankName'] ?? $firstAccount['bank'] ?? 'Monnify');
            $acctNumber = (string) ($firstAccount['accountNumber'] ?? $firstAccount['account_number'] ?? '');
            $acctName = (string) ($body['accountName'] ?? $payload['accountName']);
            $status = (string) ($body['status'] ?? 'active');

            return new VirtualAccountCreationResult(
                true,
                gateway: 'monnify',
                accountNumber: $acctNumber,
                bankName: $bankName,
                accountName: $acctName,
                status: strtolower($status) === 'active' ? 'active' : 'pending',
                reference: $accountReference,
                providerAccountReference: $body['accountReference'] ?? $accountReference,
                meta: [
                    'raw' => [
                        'accountReference' => $accountReference,
                    ],
                ],
            );
        } catch (\Throwable $e) {
            Log::error('Monnify virtual account exception', ['error' => $e->getMessage()]);

            return new VirtualAccountCreationResult(false, gateway: 'monnify', message: 'Monnify is currently unavailable');
        }
    }

    public function syncStatus(VirtualAccount $virtualAccount): ?VirtualAccountCreationResult
    {
        return null;
    }
}
