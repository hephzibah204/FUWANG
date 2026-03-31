<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\AccountBalance;
use App\Models\ApiCenter;
use App\Models\BankDetail;
use App\Models\PaymentGateway;
use App\Services\VirtualAccounts\VirtualAccountService;
use App\Support\DbTable;

class FundingController extends Controller
{
    /**
     * Get active payment gateways for the frontend.
     */
    public function getActiveGateways()
    {
        $hasTable = Schema::hasTable('payment_gateways') && DbTable::isBaseTable('payment_gateways');
        if ($hasTable && PaymentGateway::count() === 0) {
            try {
                (new \Database\Seeders\PaymentGatewaySeeder())->run();
            } catch (\Throwable $e) {
                Log::error('Failed to seed gateways: ' . $e->getMessage());
            }
        }

        $gateways = PaymentGateway::where('is_active', true)
            ->orderBy('priority', 'asc')
            ->get(['name', 'display_name', 'logo_url', 'is_active', 'config']);

        $apiCenter = ApiCenter::first();
        $gateways->transform(function ($gateway) use ($apiCenter) {
            $config = (array) ($gateway->config ?? []);
            $publicConfig = [];

            if ($gateway->name === 'paystack') {
                $publicConfig['public_key'] = $apiCenter?->paystack_public_key ?? $config['public_key'] ?? null;
            } elseif ($gateway->name === 'flutterwave') {
                $publicConfig['public_key'] = $apiCenter?->flutterwave_public_key ?? $config['public_key'] ?? null;
            } elseif ($gateway->name === 'monnify') {
                $publicConfig['api_key'] = $apiCenter?->monnify_api_key ?? $config['api_key'] ?? null;
                $publicConfig['contract_code'] = $apiCenter?->monnify_contract_code ?? $config['contract_code'] ?? null;
            }

            $gateway->config = $publicConfig;
            return $gateway;
        });

        return response()->json(['status' => true, 'gateways' => $gateways]);
    }

    public function validateProviderConfig(Request $request)
    {
        $name = $request->input('gateway');
        if ($name === 'wallet') {
            return response()->json(['status' => true, 'message' => 'Wallet balance is sufficient']);
        }
        
        $gateway = PaymentGateway::where('name', $name)->first();
        if (!$gateway) {
            return response()->json(['status' => false, 'message' => 'Provider not found']);
        }

        if (!$gateway->is_active) {
            return response()->json(['status' => false, 'message' => 'Selected provider is currently disabled']);
        }

        $apiCenter = ApiCenter::first();
        $isConfigured = false;

        if ($name === 'paystack') {
            $isConfigured = (bool) ($apiCenter?->paystack_public_key && $apiCenter?->paystack_secret_key);
        } elseif ($name === 'flutterwave') {
            $isConfigured = (bool) ($apiCenter?->flutterwave_public_key && $apiCenter?->flutterwave_secret_key);
        } elseif ($name === 'monnify') {
            $isConfigured = (bool) ($apiCenter?->monnify_api_key && $apiCenter?->monnify_secret_key);
        } elseif ($name === 'payvessel') {
            $isConfigured = (bool) ($apiCenter?->payvessel_api_key && $apiCenter?->payvessel_secret_key);
        } elseif ($name === 'paymentpoint') {
            $isConfigured = (bool) ($apiCenter?->paypoint_api_key && $apiCenter?->paypoint_secret_key);
        }

        if (!$isConfigured) {
            Log::warning("API keys missing for gateway: {$name}");
            return response()->json(['status' => false, 'message' => 'No API key set for selected provider']);
        }

        Log::info("API configuration validated for gateway: {$name}");
        return response()->json(['status' => true, 'message' => 'Configuration validated']);
    }

    /**
     * Show the bank details for manual funding.
     */
    public function bankDetails()
    {
        $details = DB::table('manual_funding')->first();
        return response()->json(['status' => true, 'details' => $details]);
    }

    public function fundPage()
    {
        return view('wallet.fund');
    }

    /**
     * User submits a manual funding request (proof of transfer).
     */
    public function submitRequest(Request $request)
    {
        $request->validate([
            'amount'    => 'required|numeric|min:100',
            'reference' => 'required|string|max:100',
        ]);

        $user = Auth::user();

        $userRef = trim((string) $request->reference);
        $safeRef = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $userRef));
        $safeRef = $safeRef ?: 'REF';
        $reference = 'MANUAL-' . substr($safeRef, 0, 24) . '-' . strtoupper(bin2hex(random_bytes(3)));

        DB::table('fundings')->insert([
            'email' => $user->email,
            'amount' => (float) $request->amount,
            'reference' => $reference,
            'description' => 'Manual Funding Request. User Ref: ' . $userRef,
            'funding_type' => 'Manual Funding Request',
            'fullname' => $user->fullname ?? $user->username,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (Schema::hasTable('funding_history') && DbTable::isBaseTable('funding_history')) {
            DB::table('funding_history')->insert([
                'funding_type' => 'Manual Funding Request',
                'amount' => $request->amount,
                'email' => $user->email,
                'fullname' => $user->fullname ?? $user->username,
                'date' => now(),
            ]);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Your funding request of ₦' . number_format($request->amount, 2)
                       . ' has been submitted. Reference: ' . $request->reference
                       . '. An admin will credit your wallet shortly.',
        ]);
    }

    public function reservePalmpay(Request $request)
    {
        $user = Auth::user();

        $number = $user->number ?? $request->input('number');
        if (!$number || !preg_match('/^\d{11}$/', (string) $number)) {
            return response()->json(['status' => false, 'message' => 'Update your profile phone number first.']);
        }

        $apiCenter = ApiCenter::first();

        $apiKey = $apiCenter->paypoint_api_key ?? null;
        $secretKey = $apiCenter->paypoint_secret_key ?? null;
        $businessId = $apiCenter->paypoint_businessid ?? null;
        $endpoint = $apiCenter->paypoint_endpoint ?? null;

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
            return response()->json(['status' => false, 'message' => 'PalmPay gateway is not configured.']);
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
                Log::warning('PalmPay reserve account failed', [
                    'status' => $res->status(),
                    'body' => $res->body(),
                ]);
                return response()->json(['status' => false, 'message' => 'PalmPay gateway error.']);
            }

            if (($data['status'] ?? null) !== 'success') {
                return response()->json(['status' => false, 'message' => $data['message'] ?? 'Unable to generate PalmPay account.']);
            }

            $accountNumber = $data['bankAccounts'][0]['accountNumber'] ?? null;
            if (!$accountNumber) {
                return response()->json(['status' => false, 'message' => 'PalmPay returned no account number.']);
            }

            BankDetail::updateOrCreate(
                ['email' => $user->email],
                ['palmpay' => (string) $accountNumber]
            );

            return response()->json([
                'status' => true,
                'message' => 'PalmPay account generated successfully.',
                'accountNumber' => (string) $accountNumber,
            ]);
        } catch (\Throwable $e) {
            Log::error('PalmPay reserve account exception', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'PalmPay gateway is currently unavailable.']);
        }
    }

    public function ensureAutoFundingAccounts(Request $request)
    {
        $user = Auth::user();

        $result = app(VirtualAccountService::class)->ensureAccounts($user, false);

        if (!empty($result['accounts'])) {
            return response()->json([
                'status' => true,
                'accounts' => $result['accounts'],
                'providers' => $result['providers'],
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'No auto-funding accounts available. Configure PayVessel/Monnify first.',
            'providers' => $result['providers'] ?? [],
        ]);
    }

    public function listVirtualAccounts(Request $request)
    {
        $user = Auth::user();

        $accounts = app(VirtualAccountService::class)->presentAccounts($user);

        return response()->json([
            'status' => true,
            'accounts' => $accounts,
        ]);
    }

    public function regenerateAutoFundingAccounts(Request $request)
    {
        $user = Auth::user();
        if (($user->role ?? 'user') !== 'admin') {
            return response()->json(['status' => false, 'message' => 'Forbidden'], 403);
        }

        $result = app(VirtualAccountService::class)->ensureAccounts($user, true);

        if (!empty($result['accounts'])) {
            return response()->json([
                'status' => true,
                'accounts' => $result['accounts'],
                'providers' => $result['providers'],
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'No auto-funding accounts available. Configure PayVessel/Monnify first.',
            'providers' => $result['providers'] ?? [],
        ]);
    }
}
