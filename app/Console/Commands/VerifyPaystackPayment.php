<?php

namespace App\Console\Commands;

use App\Models\ApiCenter;
use App\Models\PaymentIntent;
use App\Models\PaymentWebhookEvent;
use App\Models\User;
use App\Services\WalletService;
use App\Support\PaymentProviderCredentials;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class VerifyPaystackPayment extends Command
{
    protected $signature = 'payments:verify-paystack {reference : The transaction reference or Paystack ref} {--email= : User email if reference is not linked to an intent}';
    protected $description = 'Verify a Paystack transaction directly with Paystack API and credit the user wallet';

    public function handle(WalletService $walletService): int
    {
        $reference = trim((string) $this->argument('reference'));
        $manualEmail = trim((string) $this->option('email'));

        if ($reference === '') {
            $this->error('Transaction reference is required.');
            return self::FAILURE;
        }

        $this->info("Verifying transaction '{$reference}' with Paystack...");

        $apiCenter = ApiCenter::first();
        $secretKey = PaymentProviderCredentials::paystack($apiCenter)['secret_key'];
        if (!$secretKey) {
            $this->error('Paystack secret key is not configured.');
            return self::FAILURE;
        }

        $res = Http::timeout(45)
            ->withHeaders(['Authorization' => 'Bearer ' . $secretKey])
            ->get('https://api.paystack.co/transaction/verify/' . urlencode($reference));

        if (!$res->successful()) {
            $this->error('Paystack API query failed: ' . $res->status() . ' - ' . $res->body());
            return self::FAILURE;
        }

        $payload = $res->json();
        $data = $payload['data'] ?? null;
        if (!$data) {
            $this->error('Invalid response payload from Paystack.');
            return self::FAILURE;
        }

        $status = Str::lower((string) ($data['status'] ?? ''));
        if ($status !== 'success') {
            $this->error("Transaction is not successful on Paystack. Current status: '{$status}'.");
            return self::FAILURE;
        }

        $amountKobo = (int) ($data['amount'] ?? 0);
        $amount = round($amountKobo / 100, 2);
        $paidEmail = Str::lower(trim((string) ($data['customer']['email'] ?? '')));
        $gatewayRef = (string) ($data['reference'] ?? $reference);

        $this->line("Gateway Status: {$status} | Amount: NGN {$amount} | Customer Email: {$paidEmail} | Gateway Ref: {$gatewayRef}");

        // Check if already processed
        if (DB::table('fundings')->where('reference', $reference)->orWhere('reference', $gatewayRef)->exists()) {
            $this->warn("Payment '{$reference}' / '{$gatewayRef}' has already been processed in fundings table.");
            return self::SUCCESS;
        }

        // Resolve user
        $user = null;
        $intent = PaymentIntent::where('reference', $reference)->orWhere('reference', $gatewayRef)->first();
        if ($intent && $intent->user_id) {
            $user = User::find($intent->user_id);
            if ($user) {
                $this->info("Found User via PaymentIntent #{$intent->id}: {$user->email} (ID: {$user->id})");
            }
        }

        if (!$user && $paidEmail !== '') {
            $user = User::whereRaw('LOWER(email) = ?', [$paidEmail])->first();
            if ($user) {
                $this->info("Found User via Paystack customer email: {$user->email} (ID: {$user->id})");
            }
        }

        if (!$user && $manualEmail !== '') {
            $user = User::whereRaw('LOWER(email) = ?', [strtolower($manualEmail)])->first();
            if ($user) {
                $this->info("Found User via manual --email option: {$user->email} (ID: {$user->id})");
            }
        }

        if (!$user) {
            $this->error("Could not resolve Fuwa user for this payment. Please provide the user email using --email=<user_email>.");
            return self::FAILURE;
        }

        // Credit wallet
        try {
            DB::transaction(function () use ($walletService, $user, $amount, $reference, $gatewayRef, $intent) {
                $credit = $walletService->credit($user, $amount, 'Wallet Funding – Paystack', $reference);
                if (!$credit['ok']) {
                    throw new \RuntimeException('Wallet credit was declined.');
                }

                // Handle referral
                try {
                    app(\App\Services\Referrals\ReferralService::class)->handleFunding($user, $amount, $reference, 'Wallet Funding – Paystack');
                } catch (\Throwable $e) {
                    // Ignore referral error
                }

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

                // Update webhook event if one exists
                PaymentWebhookEvent::where('provider', 'paystack')
                    ->where(function ($q) use ($reference, $gatewayRef) {
                        $q->where('reference', $reference)
                          ->orWhere('reference', $gatewayRef)
                          ->orWhere('provider_event_id', $reference)
                          ->orWhere('provider_event_id', $gatewayRef);
                    })
                    ->update([
                        'processing_status' => 'succeeded',
                        'processed_at' => now(),
                        'processing_error' => null,
                    ]);
            });

            $this->info("SUCCESS! Credited NGN " . number_format($amount, 2) . " to user {$user->email} (Reference: {$reference}).");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Failed to credit wallet: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
