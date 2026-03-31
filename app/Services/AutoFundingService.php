<?php

namespace App\Services;

use App\Models\ApiCenter;
use App\Models\BankDetail;
use App\Models\PaymentGateway;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AutoFundingService
{
    public function ensureAccounts(User $user, bool $force = false): array
    {
        $bankDetail = BankDetail::firstOrNew(['email' => $user->email]);
        $bankDetail->email = $user->email;

        $apiCenter = ApiCenter::first();

        if ($force) {
            $bankDetail->palmpay = null;
            $bankDetail->psb9 = null;
            $bankDetail->GTBank_account = null;
            $bankDetail->Moniepoint_account = null;
            $bankDetail->Wema_account = null;
            $bankDetail->Sterling_account = null;
            $bankDetail->account_reference = null;
        }

        $monnify = $this->ensureMonnifyAccounts($user, $bankDetail, $apiCenter);
        $payvessel = $this->ensurePayvesselAccount($user, $bankDetail, $apiCenter);
        $palmpay = $this->ensurePalmPayAccount($user, $bankDetail, $apiCenter);

        if ($bankDetail->isDirty()) {
            $bankDetail->save();
        }

        return [
            'status' => true,
            'accounts' => $this->extractAccounts($bankDetail),
            'providers' => [
                'monnify' => $monnify,
                'payvessel' => $payvessel,
                'palmpay' => $palmpay,
            ],
        ];
    }

    private function extractAccounts(BankDetail $detail): array
    {
        $map = [
            'palmpay' => ['label' => 'PalmPay', 'account' => $detail->palmpay, 'group' => 'palmpay', 'group_label' => 'PalmPay'],
            'psb9' => ['label' => '9PSB / PayVessel', 'account' => $detail->psb9, 'group' => 'payvessel', 'group_label' => 'PayVessel'],
            'GTBank_account' => ['label' => 'GTBank', 'account' => $detail->GTBank_account, 'group' => 'monnify', 'group_label' => 'Monnify'],
            'Moniepoint_account' => ['label' => 'Moniepoint', 'account' => $detail->Moniepoint_account, 'group' => 'monnify', 'group_label' => 'Monnify'],
            'Wema_account' => ['label' => 'Wema', 'account' => $detail->Wema_account, 'group' => 'monnify', 'group_label' => 'Monnify'],
            'Sterling_account' => ['label' => 'Sterling', 'account' => $detail->Sterling_account, 'group' => 'monnify', 'group_label' => 'Monnify'],
        ];

        $accounts = [];
        foreach ($map as $key => $row) {
            if (!empty($row['account'])) {
                $accounts[] = [
                    'provider' => $key,
                    'provider_group' => $row['group'],
                    'provider_group_label' => $row['group_label'],
                    'bank' => $row['label'],
                    'accountNumber' => (string) $row['account'],
                    'accountName' => $detail->account_name ?: null,
                ];
            }
        }
        return $accounts;
    }

    private function ensurePayvesselAccount(User $user, BankDetail $detail, ?ApiCenter $apiCenter): array
    {
        if (!empty($detail->psb9)) {
            return ['ok' => true, 'skipped' => true];
        }

        $endpoint = $apiCenter?->payvessel_endpoint;
        $apiKey = $apiCenter?->payvessel_api_key;
        $businessId = $apiCenter?->payvessel_businessid;

        if (!$endpoint || !$apiKey) {
            $gw = PaymentGateway::where('name', 'payvessel')->first();
            $endpoint = $endpoint ?: ($gw->config['endpoint'] ?? null);
            $apiKey = $apiKey ?: ($gw->config['api_key'] ?? null);
            $businessId = $businessId ?: ($gw->config['business_id'] ?? null);
        }

        if (!$endpoint || !$apiKey) {
            return ['ok' => false, 'message' => 'PayVessel not configured'];
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
                ])
                ->post($endpoint, $payload);

            $data = $res->json();

            if (!$res->successful()) {
                Log::warning('PayVessel reserve account failed', ['status' => $res->status(), 'body' => $res->body()]);
                return ['ok' => false, 'message' => 'PayVessel gateway error'];
            }

            $acct = $data['data']['accountNumber']
                ?? $data['data']['account_number']
                ?? $data['accountNumber']
                ?? $data['account_number']
                ?? $data['bankAccounts'][0]['accountNumber']
                ?? null;

            $acctName = $data['data']['accountName']
                ?? $data['data']['account_name']
                ?? $data['accountName']
                ?? $data['account_name']
                ?? null;

            if ($acct) {
                $detail->psb9 = (string) $acct;
                if ($acctName && !$detail->account_name) {
                    $detail->account_name = (string) $acctName;
                }
                return ['ok' => true];
            }

            return ['ok' => false, 'message' => $data['message'] ?? 'PayVessel response not recognized'];
        } catch (\Throwable $e) {
            Log::error('PayVessel reserve account exception', ['error' => $e->getMessage()]);
            return ['ok' => false, 'message' => 'PayVessel unavailable'];
        }
    }

    private function ensureMonnifyAccounts(User $user, BankDetail $detail, ?ApiCenter $apiCenter): array
    {
        if (!empty($detail->GTBank_account) || !empty($detail->Wema_account) || !empty($detail->Sterling_account) || !empty($detail->Moniepoint_account)) {
            return ['ok' => true, 'skipped' => true];
        }

        $apiKey = $apiCenter?->monnify_api_key;
        $secretKey = $apiCenter?->monnify_secret_key;
        $contractCode = $apiCenter?->monnify_contract_code;
        $authEndpoint = $apiCenter?->monnify_endpoint_auth;
        $reserveEndpoint = $apiCenter?->monnify_endpoint_reserve;

        if ((!$apiKey || !$secretKey || !$contractCode) && PaymentGateway::where('name', 'monnify')->exists()) {
            $gw = PaymentGateway::where('name', 'monnify')->first();
            $apiKey = $apiKey ?: ($gw->config['api_key'] ?? null);
            $secretKey = $secretKey ?: ($gw->config['secret_key'] ?? null);
            $contractCode = $contractCode ?: ($gw->config['contract_code'] ?? null);
        }

        $authEndpoint = $authEndpoint ?: 'https://api.monnify.com/api/v1/auth/login';
        $reserveEndpoint = $reserveEndpoint ?: 'https://api.monnify.com/api/v2/bank-transfer/reserved-accounts';

        if (!$apiKey || !$secretKey || !$contractCode) {
            return ['ok' => false, 'message' => 'Monnify not configured'];
        }

        $accountReference = $detail->account_reference ?: ('FUWA-' . strtoupper(bin2hex(random_bytes(6))));
        $detail->account_reference = $accountReference;
        $detail->contract_code = $contractCode;
        $detail->currency_code = $detail->currency_code ?: 'NGN';

        try {
            $basic = base64_encode($apiKey . ':' . $secretKey);
            $authRes = Http::timeout(45)->withHeaders([
                'Authorization' => 'Basic ' . $basic,
            ])->post($authEndpoint);

            $authData = $authRes->json();
            if (!$authRes->successful()) {
                Log::warning('Monnify auth failed', ['status' => $authRes->status(), 'body' => $authRes->body()]);
                return ['ok' => false, 'message' => 'Monnify auth failed'];
            }

            $token = $authData['responseBody']['accessToken'] ?? $authData['responseBody']['token'] ?? null;
            if (!$token) {
                return ['ok' => false, 'message' => 'Monnify token missing'];
            }

            $payload = [
                'accountReference' => $accountReference,
                'accountName' => $detail->account_name ?: ($user->fullname ?? $user->username ?? 'Fuwa Wallet'),
                'currencyCode' => 'NGN',
                'contractCode' => $contractCode,
                'customerEmail' => $user->email,
                'customerName' => $user->fullname ?? $user->username ?? $user->email,
                'getAllAvailableBanks' => true,
            ];

            $reserveRes = Http::timeout(60)->withToken($token)->post($reserveEndpoint, $payload);
            $reserveData = $reserveRes->json();

            if (!$reserveRes->successful()) {
                Log::warning('Monnify reserve failed', ['status' => $reserveRes->status(), 'body' => $reserveRes->body()]);
                return ['ok' => false, 'message' => 'Monnify reserve failed'];
            }

            $responseBody = $reserveData['responseBody'] ?? $reserveData['data'] ?? $reserveData;
            $accounts = $responseBody['accounts'] ?? $responseBody['bankAccounts'] ?? $responseBody['reservedAccounts'] ?? [];

            $detail->account_name = $detail->account_name ?: ($responseBody['accountName'] ?? $payload['accountName']);
            $detail->status = (string) ($responseBody['status'] ?? $detail->status ?? 'active');

            foreach ((array) $accounts as $acc) {
                $bank = (string) ($acc['bankName'] ?? $acc['bank'] ?? '');
                $acct = (string) ($acc['accountNumber'] ?? $acc['account_number'] ?? '');
                if (!$acct) {
                    continue;
                }
                if (stripos($bank, 'GT') !== false) {
                    $detail->GTBank_account = $acct;
                } elseif (stripos($bank, 'WEMA') !== false) {
                    $detail->Wema_account = $acct;
                } elseif (stripos($bank, 'STERLING') !== false) {
                    $detail->Sterling_account = $acct;
                } elseif (stripos($bank, 'MONIEPOINT') !== false) {
                    $detail->Moniepoint_account = $acct;
                } elseif (!$detail->psb9) {
                    $detail->psb9 = $acct;
                }
            }

            return ['ok' => true];
        } catch (\Throwable $e) {
            Log::error('Monnify reserve exception', ['error' => $e->getMessage()]);
            return ['ok' => false, 'message' => 'Monnify unavailable'];
        }
    }

    private function ensurePalmPayAccount(User $user, BankDetail $detail, ?ApiCenter $apiCenter): array
    {
        if (!empty($detail->palmpay)) {
            return ['ok' => true, 'skipped' => true];
        }

        $number = $user->number;
        if (!$number || !preg_match('/^\d{11}$/', (string) $number)) {
            return ['ok' => false, 'message' => 'Valid phone number required for PalmPay'];
        }

        $apiKey = $apiCenter->paypoint_api_key ?? null;
        $secretKey = $apiCenter->paypoint_secret_key ?? null;
        $businessId = $apiCenter->paypoint_businessid ?? null;
        $endpoint = $apiCenter->paypoint_endpoint ?? null;

        if ((!$apiKey || !$secretKey || !$businessId || !$endpoint) && \Illuminate\Support\Facades\Schema::hasTable('paypoint_details')) {
            $row = \Illuminate\Support\Facades\DB::table('paypoint_details')->first();
            if ($row) {
                $apiKey = $apiKey ?: ($row->paypoint_api_key ?? null);
                $secretKey = $secretKey ?: ($row->paypoint_secret_key ?? null);
                $businessId = $businessId ?: ($row->paypoint_businessid ?? null);
                $endpoint = $endpoint ?: ($row->paypoint_endpoint ?? null);
            }
        }

        if (!$apiKey || !$secretKey || !$businessId || !$endpoint) {
            return ['ok' => false, 'message' => 'PalmPay not configured'];
        }

        $payload = [
            'email' => $user->email,
            'name' => $user->fullname ?? $user->username ?? $user->email,
            'phoneNumber' => (string) $number,
            'bankCode' => ['20946'],
            'businessId' => (string) $businessId,
        ];

        try {
            $res = Http::withHeaders([
                'Authorization' => 'Bearer ' . $secretKey,
                'Content-Type' => 'application/json',
                'api-key' => $apiKey,
            ])->timeout(60)->post($endpoint, $payload);

            $data = $res->json();

            if (!$res->successful()) {
                Log::warning('PalmPay reserve account failed', ['status' => $res->status(), 'body' => $res->body()]);
                return ['ok' => false, 'message' => 'PalmPay gateway error'];
            }

            if (($data['status'] ?? null) !== 'success') {
                return ['ok' => false, 'message' => $data['message'] ?? 'Unable to generate PalmPay account.'];
            }

            $accountNumber = $data['bankAccounts'][0]['accountNumber'] ?? null;
            if ($accountNumber) {
                $detail->palmpay = (string) $accountNumber;
                return ['ok' => true];
            }

            return ['ok' => false, 'message' => 'PalmPay returned no account number.'];
        } catch (\Throwable $e) {
            Log::error('PalmPay reserve account exception', ['error' => $e->getMessage()]);
            return ['ok' => false, 'message' => 'PalmPay gateway is currently unavailable.'];
        }
    }
}
