<?php

namespace App\Services\VirtualAccounts\Providers;

use App\Models\User;
use App\Models\VirtualAccount;
use App\Services\VirtualAccounts\Dto\VirtualAccountCreationResult;
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
        $cfg = $this->getGatewayConfig('monnify');
        $apiCenter = $cfg['apiCenter'];
        $apiKey = $apiCenter?->monnify_api_key ?: ($cfg['config']['api_key'] ?? null);
        $secretKey = $apiCenter?->monnify_secret_key ?: ($cfg['config']['secret_key'] ?? null);
        $contractCode = $apiCenter?->monnify_contract_code ?: ($cfg['config']['contract_code'] ?? null);
        return (bool) ($apiKey && $secretKey && $contractCode);
    }

    public function create(User $user): VirtualAccountCreationResult
    {
        $cfg = $this->getGatewayConfig('monnify');
        $apiCenter = $cfg['apiCenter'];

        $apiKey = $apiCenter?->monnify_api_key ?: ($cfg['config']['api_key'] ?? null);
        $secretKey = $apiCenter?->monnify_secret_key ?: ($cfg['config']['secret_key'] ?? null);
        $contractCode = $apiCenter?->monnify_contract_code ?: ($cfg['config']['contract_code'] ?? null);
        $authEndpoint = $apiCenter?->monnify_endpoint_auth ?: 'https://api.monnify.com/api/v1/auth/login';
        $reserveEndpoint = $apiCenter?->monnify_endpoint_reserve ?: 'https://api.monnify.com/api/v2/bank-transfer/reserved-accounts';

        if (!$apiKey || !$secretKey || !$contractCode) {
            return new VirtualAccountCreationResult(false, gateway: 'monnify', message: 'Monnify is not configured');
        }

        $accountReference = 'FUWA-' . strtoupper(bin2hex(random_bytes(6)));

        try {
            $basic = base64_encode($apiKey . ':' . $secretKey);
            $authRes = Http::timeout(45)->withHeaders([
                'Authorization' => 'Basic ' . $basic,
                'Accept' => 'application/json',
            ])->post($authEndpoint);

            if (!$authRes->successful()) {
                Log::warning('Monnify auth failed', ['status' => $authRes->status(), 'body' => $authRes->body()]);
                return new VirtualAccountCreationResult(false, gateway: 'monnify', message: 'Monnify auth failed');
            }

            $authJson = $authRes->json();
            $token = $authJson['responseBody']['accessToken'] ?? $authJson['responseBody']['token'] ?? null;
            if (!$token) {
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

            $reserveRes = Http::timeout(60)->withToken($token)->post($reserveEndpoint, $payload);
            if (!$reserveRes->successful()) {
                Log::warning('Monnify reserve failed', ['status' => $reserveRes->status(), 'body' => $reserveRes->body()]);
                return new VirtualAccountCreationResult(false, gateway: 'monnify', message: 'Monnify reserve failed');
            }

            $reserveJson = $reserveRes->json();
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

            if (!$firstAccount) {
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

