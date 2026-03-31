<?php

namespace App\Services\VirtualAccounts\Providers;

use App\Models\User;
use App\Models\VirtualAccount;
use App\Services\VirtualAccounts\Dto\VirtualAccountCreationResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PalmpayVirtualAccountProvider extends AbstractHttpProvider
{
    public function name(): string
    {
        return 'palmpay';
    }

    public function supportsVirtualAccounts(): bool
    {
        $cfg = $this->getGatewayConfig('paymentpoint');
        $apiCenter = $cfg['apiCenter'];

        $apiKey = $apiCenter?->paypoint_api_key;
        $secretKey = $apiCenter?->paypoint_secret_key;
        $businessId = $apiCenter?->paypoint_businessid;
        $endpoint = $apiCenter?->paypoint_endpoint;

        if ($apiKey && $secretKey && $businessId && $endpoint) {
            return true;
        }

        if (Schema::hasTable('paypoint_details')) {
            $row = DB::table('paypoint_details')->first();
            return (bool) ($row?->paypoint_api_key && $row?->paypoint_secret_key && $row?->paypoint_businessid && $row?->paypoint_endpoint);
        }

        return false;
    }

    public function create(User $user): VirtualAccountCreationResult
    {
        $cfg = $this->getGatewayConfig('paymentpoint');
        $apiCenter = $cfg['apiCenter'];

        $number = $user->number;
        if (!$number || !preg_match('/^\d{11}$/', (string) $number)) {
            return new VirtualAccountCreationResult(false, gateway: 'palmpay', message: 'Valid phone number required for PalmPay');
        }

        $apiKey = $apiCenter?->paypoint_api_key ?? null;
        $secretKey = $apiCenter?->paypoint_secret_key ?? null;
        $businessId = $apiCenter?->paypoint_businessid ?? null;
        $endpoint = $apiCenter?->paypoint_endpoint ?? null;

        if ((!$apiKey || !$secretKey || !$businessId || !$endpoint) && Schema::hasTable('paypoint_details')) {
            $row = DB::table('paypoint_details')->first();
            if ($row) {
                $apiKey = $apiKey ?: ($row->paypoint_api_key ?? null);
                $secretKey = $secretKey ?: ($row->paypoint_secret_key ?? null);
                $businessId = $businessId ?: ($row->paypoint_businessid ?? null);
                $endpoint = $endpoint ?: ($row->paypoint_endpoint ?? null);
            }
        }

        if (!$apiKey || !$secretKey || !$businessId || !$endpoint) {
            return new VirtualAccountCreationResult(false, gateway: 'palmpay', message: 'PalmPay not configured');
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
                'Accept' => 'application/json',
            ])->timeout(60)->post($endpoint, $payload);

            $json = $res->json();
            if (!$res->successful()) {
                Log::warning('PalmPay reserve account failed', ['status' => $res->status(), 'body' => $res->body()]);
                return new VirtualAccountCreationResult(false, gateway: 'palmpay', message: 'PalmPay gateway error');
            }

            if (($json['status'] ?? null) !== 'success') {
                return new VirtualAccountCreationResult(false, gateway: 'palmpay', message: $json['message'] ?? 'Unable to generate PalmPay account');
            }

            $accountNumber = $json['bankAccounts'][0]['accountNumber'] ?? null;
            if (!$accountNumber) {
                return new VirtualAccountCreationResult(false, gateway: 'palmpay', message: 'PalmPay returned no account number');
            }

            return new VirtualAccountCreationResult(
                true,
                gateway: 'palmpay',
                accountNumber: (string) $accountNumber,
                bankName: 'PalmPay',
                accountName: $user->fullname ?? $user->username ?? null,
                status: 'active',
                meta: [
                    'business_id' => $businessId,
                ],
            );
        } catch (\Throwable $e) {
            Log::error('PalmPay reserve account exception', ['error' => $e->getMessage()]);
            return new VirtualAccountCreationResult(false, gateway: 'palmpay', message: 'PalmPay is currently unavailable');
        }
    }

    public function syncStatus(VirtualAccount $virtualAccount): ?VirtualAccountCreationResult
    {
        return null;
    }
}

