<?php

namespace App\Http\Controllers;

use App\Models\ApiCenter;
use App\Services\WalletService;
use App\Models\PaymentIntent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaymentVerificationController extends Controller
{
    private function alreadyProcessed(string $reference): bool
    {
        if (DB::table('fundings')->where('reference', $reference)->exists()) {
            return true;
        }
        if (DB::getSchemaBuilder()->hasTable('payment_transactions')) {
            return DB::table('payment_transactions')->where('reference', $reference)->exists();
        }
        return false;
    }

    public function verifyPaystack(Request $request)
    {
        $request->validate([
            'reference' => ['required', 'string', 'max:120'],
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $reference = trim((string) $request->reference);
        if ($this->alreadyProcessed($reference)) {
            return response()->json(['status' => true, 'message' => 'Already processed', 'reference' => $reference]);
        }

        $apiCenter = ApiCenter::first();
        $secretKey = $apiCenter?->paystack_secret_key;
        if (!$secretKey) {
            return response()->json(['status' => false, 'message' => 'Paystack is not configured.'], 422);
        }

        $res = Http::timeout(45)
            ->withHeaders(['Authorization' => 'Bearer ' . $secretKey])
            ->get('https://api.paystack.co/transaction/verify/' . urlencode($reference));

        if (!$res->successful()) {
            return response()->json(['status' => false, 'message' => 'Unable to verify payment.'], 502);
        }

        $payload = $res->json();
        $data = $payload['data'] ?? null;

        $status = Str::lower((string) ($data['status'] ?? ''));
        if ($status !== 'success') {
            return response()->json(['status' => false, 'message' => 'Payment not successful.'], 422);
        }

        $currency = strtoupper((string) ($data['currency'] ?? ''));
        if ($currency !== 'NGN') {
            return response()->json(['status' => false, 'message' => 'Unsupported currency.'], 422);
        }

        $paidEmail = Str::lower((string) ($data['customer']['email'] ?? ''));
        if (!$paidEmail || Str::lower((string) $user->email) !== $paidEmail) {
            return response()->json(['status' => false, 'message' => 'Payment email mismatch.'], 422);
        }

        $amountKobo = (int) ($data['amount'] ?? 0);
        $amount = round($amountKobo / 100, 2);
        if ($amount <= 0) {
            return response()->json(['status' => false, 'message' => 'Invalid payment amount.'], 422);
        }

        $intent = PaymentIntent::where('reference', $reference)->first();
        if ($intent && $intent->amount_expected !== null && (float) $intent->amount_expected != (float) $amount) {
            return response()->json(['status' => false, 'message' => 'Amount mismatch.'], 422);
        }

        try {
            DB::transaction(function () use ($user, $amount, $reference) {
                if ($this->alreadyProcessed($reference)) {
                    return;
                }

                app(WalletService::class)->credit($user, $amount, 'Wallet Funding – Paystack', $reference);

                // Handle referral logic
                try {
                    app(\App\Services\Referrals\ReferralService::class)->handleFunding($user, $amount, $reference, 'Wallet Funding – Paystack');
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Referral funding handler failed', ['error' => $e->getMessage()]);
                }

                $intent = PaymentIntent::where('reference', $reference)->lockForUpdate()->first();
                if ($intent) {
                    $intent->gateway = 'paystack';
                    $intent->status = 'succeeded';
                    $intent->save();
                }

                DB::table('fundings')->insert([
                    'email' => $user->email,
                    'amount' => $amount,
                    'reference' => $reference,
                    'description' => 'Funding Wallet',
                    'funding_type' => 'Card Funding',
                    'fullname' => $user->fullname ?? $user->username ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => 'Unable to apply wallet credit.'], 500);
        }

        return response()->json(['status' => true, 'message' => 'Wallet funded successfully', 'reference' => $reference, 'amount' => $amount]);
    }

    public function verifyMonnify(Request $request)
    {
        $request->validate([
            'reference' => ['required', 'string', 'max:120'],
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $reference = trim((string) $request->reference);
        if ($this->alreadyProcessed($reference)) {
            return response()->json(['status' => true, 'message' => 'Already processed', 'reference' => $reference]);
        }

        $apiCenter = ApiCenter::first();
        $apiKey = $apiCenter?->monnify_api_key;
        $secretKey = $apiCenter?->monnify_secret_key;
        if (!$apiKey || !$secretKey) {
            return response()->json(['status' => false, 'message' => 'Monnify is not configured.'], 422);
        }

        // Get Access Token
        $authRes = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($apiKey . ':' . $secretKey),
        ])->post('https://api.monnify.com/api/v1/auth/login');

        if (!$authRes->successful()) {
            return response()->json(['status' => false, 'message' => 'Unable to authenticate with Monnify.'], 502);
        }

        $accessToken = $authRes->json()['responseBody']['accessToken'] ?? null;
        if (!$accessToken) {
            return response()->json(['status' => false, 'message' => 'Failed to get Monnify access token.'], 502);
        }

        // Verify Transaction
        $res = Http::withHeaders(['Authorization' => 'Bearer ' . $accessToken])
            ->get('https://api.monnify.com/api/v1/merchant/transactions/query?transactionReference=' . urlencode($reference));

        if (!$res->successful()) {
            return response()->json(['status' => false, 'message' => 'Unable to verify payment with Monnify.'], 502);
        }

        $data = $res->json()['responseBody'] ?? null;
        if (!$data) {
            return response()->json(['status' => false, 'message' => 'Invalid response from Monnify.'], 502);
        }

        $status = strtoupper((string) ($data['paymentStatus'] ?? ''));
        if ($status !== 'PAID') {
            return response()->json(['status' => false, 'message' => 'Payment not successful. Status: ' . $status], 422);
        }

        $amount = (float) ($data['amountPaid'] ?? 0);
        $paidEmail = Str::lower((string) ($data['customer']['email'] ?? ''));

        if (Str::lower((string) $user->email) !== $paidEmail) {
            return response()->json(['status' => false, 'message' => 'Payment email mismatch.'], 422);
        }

        try {
            DB::transaction(function () use ($user, $amount, $reference) {
                if ($this->alreadyProcessed($reference)) return;

                app(WalletService::class)->credit($user, $amount, 'Wallet Funding – Monnify', $reference);

                $intent = PaymentIntent::where('reference', $reference)->lockForUpdate()->first();
                if ($intent) {
                    $intent->gateway = 'monnify';
                    $intent->status = 'succeeded';
                    $intent->save();
                }

                DB::table('fundings')->insert([
                    'email' => $user->email,
                    'amount' => $amount,
                    'reference' => $reference,
                    'description' => 'Funding Wallet',
                    'funding_type' => 'Card/Transfer Funding',
                    'fullname' => $user->fullname ?? $user->username ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => 'Unable to apply wallet credit.'], 500);
        }

        return response()->json(['status' => true, 'message' => 'Wallet funded successfully', 'reference' => $reference, 'amount' => $amount]);
    }

    public function verifyPayvessel(Request $request)
    {
        $request->validate([
            'reference' => ['required', 'string', 'max:120'],
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $reference = trim((string) $request->reference);
        if ($this->alreadyProcessed($reference)) {
            return response()->json(['status' => true, 'message' => 'Already processed', 'reference' => $reference]);
        }

        $apiCenter = ApiCenter::first();
        $apiKey = $apiCenter?->payvessel_api_key;
        $secretKey = $apiCenter?->payvessel_secret_key;
        if (!$apiKey || !$secretKey) {
            return response()->json(['status' => false, 'message' => 'Payvessel is not configured.'], 422);
        }

        $res = Http::withHeaders([
            'api-key' => $apiKey,
            'api-secret' => 'Bearer ' . $secretKey,
        ])->get('https://api.payvessel.com/api/v1/transaction/status/' . urlencode($reference));

        if (!$res->successful()) {
            return response()->json(['status' => false, 'message' => 'Unable to verify payment with Payvessel.'], 502);
        }

        $data = $res->json();
        if (($data['status'] ?? false) !== true) {
            return response()->json(['status' => false, 'message' => $data['message'] ?? 'Transaction not found or failed.'], 422);
        }

        $tx = $data['transaction'] ?? [];
        $txStatus = strtoupper((string) ($tx['status'] ?? ''));

        if ($txStatus !== 'SUCCESSFUL' && $txStatus !== 'SUCCESS') {
            return response()->json(['status' => false, 'message' => 'Payment not successful. Status: ' . $txStatus], 422);
        }

        $amount = (float) ($tx['amount'] ?? 0);
        
        try {
            DB::transaction(function () use ($user, $amount, $reference) {
                if ($this->alreadyProcessed($reference)) return;

                app(WalletService::class)->credit($user, $amount, 'Wallet Funding – Payvessel', $reference);

                $intent = PaymentIntent::where('reference', $reference)->lockForUpdate()->first();
                if ($intent) {
                    $intent->gateway = 'payvessel';
                    $intent->status = 'succeeded';
                    $intent->save();
                }

                DB::table('fundings')->insert([
                    'email' => $user->email,
                    'amount' => $amount,
                    'reference' => $reference,
                    'description' => 'Funding Wallet',
                    'funding_type' => 'Payvessel Funding',
                    'fullname' => $user->fullname ?? $user->username ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => 'Unable to apply wallet credit.'], 500);
        }

        return response()->json(['status' => true, 'message' => 'Wallet funded successfully', 'reference' => $reference, 'amount' => $amount]);
    }

    public function verifyFlutterwave(Request $request)
    {
        $request->validate([
            'transaction_id' => ['nullable', 'string', 'max:120'],
            'tx_ref' => ['nullable', 'string', 'max:120'],
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $transactionId = trim((string) ($request->transaction_id ?? ''));
        $txRef = trim((string) ($request->tx_ref ?? ''));

        if (!$transactionId && !$txRef) {
            return response()->json(['status' => false, 'message' => 'Missing transaction reference.'], 422);
        }

        $apiCenter = ApiCenter::first();
        $secretKey = $apiCenter?->flutterwave_secret_key;
        if (!$secretKey) {
            return response()->json(['status' => false, 'message' => 'Flutterwave is not configured.'], 422);
        }

        $endpoint = null;
        if ($transactionId !== '' && ctype_digit($transactionId)) {
            $endpoint = 'https://api.flutterwave.com/v3/transactions/' . $transactionId . '/verify';
        } else {
            $endpoint = 'https://api.flutterwave.com/v3/transactions/verify_by_reference?tx_ref=' . urlencode($txRef ?: $transactionId);
        }

        $res = Http::timeout(45)
            ->withHeaders(['Authorization' => 'Bearer ' . $secretKey])
            ->get($endpoint);

        if (!$res->successful()) {
            return response()->json(['status' => false, 'message' => 'Unable to verify payment.'], 502);
        }

        $payload = $res->json();
        $data = $payload['data'] ?? null;

        $status = Str::lower((string) ($data['status'] ?? ''));
        if ($status !== 'successful') {
            return response()->json(['status' => false, 'message' => 'Payment not successful.'], 422);
        }

        $currency = strtoupper((string) ($data['currency'] ?? ''));
        if ($currency !== 'NGN') {
            return response()->json(['status' => false, 'message' => 'Unsupported currency.'], 422);
        }

        $paidEmail = Str::lower((string) ($data['customer']['email'] ?? ''));
        if (!$paidEmail || Str::lower((string) $user->email) !== $paidEmail) {
            return response()->json(['status' => false, 'message' => 'Payment email mismatch.'], 422);
        }

        $reference = (string) ($data['tx_ref'] ?? $txRef ?: $transactionId);
        $reference = trim($reference);
        if ($reference === '') {
            return response()->json(['status' => false, 'message' => 'Invalid transaction reference.'], 422);
        }

        if ($this->alreadyProcessed($reference)) {
            return response()->json(['status' => true, 'message' => 'Already processed', 'reference' => $reference]);
        }

        $amount = (float) ($data['amount'] ?? 0);
        $amount = round($amount, 2);
        if ($amount <= 0) {
            return response()->json(['status' => false, 'message' => 'Invalid payment amount.'], 422);
        }

        $intent = PaymentIntent::where('reference', $reference)->first();
        if ($intent && $intent->amount_expected !== null && (float) $intent->amount_expected != (float) $amount) {
            return response()->json(['status' => false, 'message' => 'Amount mismatch.'], 422);
        }

        try {
            DB::transaction(function () use ($user, $amount, $reference) {
                if ($this->alreadyProcessed($reference)) {
                    return;
                }

                app(WalletService::class)->credit($user, $amount, 'Wallet Funding – Flutterwave', $reference);

                // Handle referral logic
                try {
                    app(\App\Services\Referrals\ReferralService::class)->handleFunding($user, $amount, $reference, 'Wallet Funding – Flutterwave');
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Referral funding handler failed', ['error' => $e->getMessage()]);
                }

                $intent = PaymentIntent::where('reference', $reference)->lockForUpdate()->first();
                if ($intent) {
                    $intent->gateway = 'flutterwave';
                    $intent->status = 'succeeded';
                    $intent->save();
                }

                DB::table('fundings')->insert([
                    'email' => $user->email,
                    'amount' => $amount,
                    'reference' => $reference,
                    'description' => 'Funding Wallet',
                    'funding_type' => 'Card Funding',
                    'fullname' => $user->fullname ?? $user->username ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => 'Unable to apply wallet credit.'], 500);
        }

        return response()->json(['status' => true, 'message' => 'Wallet funded successfully', 'reference' => $reference, 'amount' => $amount]);
    }
}
