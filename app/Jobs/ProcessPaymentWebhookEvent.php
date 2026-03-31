<?php

namespace App\Jobs;

use App\Models\ApiCenter;
use App\Models\PaymentIntent;
use App\Models\PaymentWebhookEvent;
use App\Models\User;
use App\Services\WalletService;
use App\Support\DbTable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProcessPaymentWebhookEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 8;

    public function __construct(public int $paymentWebhookEventId)
    {
    }

    public function handle(): void
    {
        $event = PaymentWebhookEvent::query()->find($this->paymentWebhookEventId);
        if (!$event) {
            return;
        }

        if (in_array($event->processing_status, ['succeeded', 'ignored'], true)) {
            return;
        }

        $lockKey = "webhook_process_{$event->provider}_{$event->provider_event_id}_{$event->reference}";
        $lock = Cache::lock($lockKey, 30); // 30s lock

        if (!$lock->get()) {
            // Already being processed elsewhere
            return;
        }

        try {
            PaymentWebhookEvent::query()
                ->where('id', $event->id)
                ->whereNotIn('processing_status', ['succeeded', 'ignored'])
                ->update(['processing_status' => 'processing']);

            if ($event->provider === 'paystack') {
                $this->processPaystack($event);
            } elseif ($event->provider === 'flutterwave') {
                $this->processFlutterwave($event);
            } elseif ($event->provider === 'payvessel') {
                $this->processPayvessel($event);
            } elseif ($event->provider === 'paymentpoint') {
                $this->processPaymentpoint($event);
            } elseif ($event->provider === 'palmpay') {
                $this->processPaymentpoint($event);
            } elseif ($event->provider === 'monnify') {
                $this->processMonnify($event);
            } else {
                $this->markIgnored($event, 'Unknown provider');
            }
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            PaymentWebhookEvent::query()->where('id', $event->id)->update([
                'processing_status' => 'failed',
                'processing_error' => $msg,
            ]);
            Log::error('Webhook processing failed: ' . $msg);
            throw $e;
        } finally {
            $lock->release();
        }
    }

    private function alreadyProcessed(string $reference): bool
    {
        if (Schema::hasTable('fundings')) {
            return DB::table('fundings')->where('reference', $reference)->exists();
        }
        if (Schema::hasTable('payment_transactions')) {
            return DB::table('payment_transactions')->where('reference', $reference)->exists();
        }
        return false;
    }

    private function markSucceeded(PaymentWebhookEvent $event): void
    {
        PaymentWebhookEvent::query()->where('id', $event->id)->update([
            'processing_status' => 'succeeded',
            'processing_error' => null,
            'processed_at' => now(),
        ]);
    }

    private function markIgnored(PaymentWebhookEvent $event, string $reason): void
    {
        PaymentWebhookEvent::query()->where('id', $event->id)->update([
            'processing_status' => 'ignored',
            'processing_error' => $reason,
            'processed_at' => now(),
        ]);
    }

    private function creditWallet(User $user, float $amount, string $reference, string $label): void
    {
        if ($this->alreadyProcessed($reference)) {
            return;
        }

        app(WalletService::class)->credit($user, $amount, $label, $reference);

        if (Schema::hasTable('fundings')) {
            DB::table('fundings')->insert([
                'email' => $user->email,
                'amount' => $amount,
                'reference' => $reference,
                'description' => 'Funding Wallet',
                'funding_type' => 'Card Funding',
                'fullname' => $user->fullname ?? $user->username ?? $user->email,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Handle referral logic
        try {
            app(\App\Services\Referrals\ReferralService::class)->handleFunding($user, $amount, $reference, $label);
        } catch (\Throwable $e) {
            Log::warning('Referral funding handler failed', ['error' => $e->getMessage()]);
        }
    }

    private function updateIntent(string $reference, string $gateway): void
    {
        $intent = PaymentIntent::query()->where('reference', $reference)->lockForUpdate()->first();
        if ($intent) {
            $intent->gateway = $gateway;
            $intent->status = 'succeeded';
            $intent->save();
        }
    }

    private function processPaystack(PaymentWebhookEvent $event): void
    {
        if (!$event->signature_valid) {
            $this->markIgnored($event, 'Invalid signature');
            return;
        }

        $payload = $event->payload;
        $evt = (string) ($payload['event'] ?? '');
        if ($evt !== 'charge.success') {
            $this->markIgnored($event, 'Ignored event');
            return;
        }

        $obj = $payload['data'] ?? [];
        $reference = trim((string) ($obj['reference'] ?? ''));
        $status = Str::lower((string) ($obj['status'] ?? ''));
        $currency = Str::upper((string) ($obj['currency'] ?? ''));
        $amountKobo = (int) ($obj['amount'] ?? 0);
        $email = Str::lower((string) ($obj['customer']['email'] ?? ''));
        $amount = round($amountKobo / 100, 2);

        if ($reference === '' || $email === '' || $status !== 'success' || $currency !== 'NGN' || $amount <= 0) {
            $this->markIgnored($event, 'Invalid payload');
            return;
        }

        if ($this->alreadyProcessed($reference)) {
            $this->markSucceeded($event);
            return;
        }

        DB::transaction(function () use ($email, $amount, $reference) {
            $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
            if (!$user) {
                throw new \RuntimeException('User not found');
            }

            $intent = PaymentIntent::query()->where('reference', $reference)->lockForUpdate()->first();
            if ($intent && $intent->amount_expected !== null && (float) $intent->amount_expected != (float) $amount) {
                throw new \RuntimeException('Amount mismatch');
            }

            $this->creditWallet($user, (float) $amount, $reference, 'Wallet Funding – Paystack');
            $this->updateIntent($reference, 'paystack');
        });

        $this->markSucceeded($event);
    }

    private function processFlutterwave(PaymentWebhookEvent $event): void
    {
        $payload = $event->payload;
        $evt = (string) ($payload['event'] ?? '');
        if ($evt !== 'charge.completed') {
            $this->markIgnored($event, 'Ignored event');
            return;
        }

        $obj = $payload['data'] ?? [];
        $txId = trim((string) ($obj['id'] ?? ''));
        $txRef = trim((string) ($obj['tx_ref'] ?? ''));

        $apiCenter = ApiCenter::first();
        $secret = $apiCenter?->flutterwave_secret_key;
        if (!$secret) {
            throw new \RuntimeException('Flutterwave is not configured');
        }

        $endpoint = ($txId !== '' && ctype_digit($txId))
            ? 'https://api.flutterwave.com/v3/transactions/' . $txId . '/verify'
            : 'https://api.flutterwave.com/v3/transactions/verify_by_reference?tx_ref=' . urlencode($txRef ?: $txId);

        $res = Http::timeout(45)->withHeaders(['Authorization' => 'Bearer ' . $secret])->get($endpoint);
        if (!$res->successful()) {
            throw new \RuntimeException('Unable to verify payment');
        }

        $ver = $res->json();
        $vd = $ver['data'] ?? null;
        $status = Str::lower((string) ($vd['status'] ?? ''));
        $currency = Str::upper((string) ($vd['currency'] ?? ''));
        $amount = round((float) ($vd['amount'] ?? 0), 2);
        $email = Str::lower((string) ($vd['customer']['email'] ?? ''));
        $reference = trim((string) ($vd['tx_ref'] ?? $txRef ?: $txId));

        if ($status !== 'successful' || $currency !== 'NGN' || $reference === '' || $email === '' || $amount <= 0) {
            $this->markIgnored($event, 'Invalid verification data');
            return;
        }

        if ($this->alreadyProcessed($reference)) {
            $this->markSucceeded($event);
            return;
        }

        DB::transaction(function () use ($email, $amount, $reference) {
            $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
            if (!$user) {
                throw new \RuntimeException('User not found');
            }

            $intent = PaymentIntent::query()->where('reference', $reference)->lockForUpdate()->first();
            if ($intent && $intent->amount_expected !== null && (float) $intent->amount_expected != (float) $amount) {
                throw new \RuntimeException('Amount mismatch');
            }

            $this->creditWallet($user, (float) $amount, $reference, 'Wallet Funding – Flutterwave');
            $this->updateIntent($reference, 'flutterwave');
        });

        $this->markSucceeded($event);
    }

    private function processPayvessel(PaymentWebhookEvent $event): void
    {
        if (!$event->signature_valid) {
            $this->markIgnored($event, 'Invalid signature');
            return;
        }

        $payload = $event->payload;
        $reference = trim((string) ($payload['transaction']['reference'] ?? ''));
        $settlementAmount = (float) ($payload['order']['settlement_amount'] ?? 0);
        $email = Str::lower((string) ($payload['customer']['email'] ?? ''));

        if ($reference === '' || $email === '' || $settlementAmount <= 0) {
            $this->markIgnored($event, 'Invalid payload');
            return;
        }

        if ($this->alreadyProcessed($reference)) {
            $this->markSucceeded($event);
            return;
        }

        DB::transaction(function () use ($email, $settlementAmount, $reference) {
            $psbAmount = DB::table('charges')->where('id', 1)->value('psb_amount') ?? 50;
            $netAmount = (float) $settlementAmount - (float) $psbAmount;
            if ($netAmount <= 0) {
                throw new \RuntimeException('Invalid net amount');
            }

            $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
            if (!$user) {
                throw new \RuntimeException('User balance record not found');
            }

            $this->creditWallet($user, (float) $netAmount, $reference, 'Wallet Funding – Automatic Funding');

            if (Schema::hasTable('fundings')) {
                DB::table('fundings')->where('reference', $reference)->update([
                    'fullname' => 'From 9PSB Bank',
                    'funding_type' => 'Automatic Funding',
                ]);
            }
        });

        $this->markSucceeded($event);
    }

    private function processPaymentpoint(PaymentWebhookEvent $event): void
    {
        if (!$event->signature_valid) {
            $this->markIgnored($event, 'Invalid signature');
            return;
        }

        $payload = $event->payload;
        $reference = trim((string) ($payload['transaction_id'] ?? ''));
        $settlementAmount = (float) ($payload['settlement_amount'] ?? 0);
        $email = Str::lower((string) ($payload['customer']['email'] ?? ''));
        $status = (string) ($payload['transaction_status'] ?? '');

        if ($status !== '' && !in_array($status, ['success', 'successful', 'SUCCESS'], true)) {
            $this->markIgnored($event, 'Ignored non-success transaction');
            return;
        }

        if ($reference === '' || $email === '' || $settlementAmount <= 0) {
            $this->markIgnored($event, 'Invalid payload');
            return;
        }

        if ($this->alreadyProcessed($reference)) {
            $this->markSucceeded($event);
            return;
        }

        DB::transaction(function () use ($email, $settlementAmount, $reference) {
            $deduction = (float) $settlementAmount * 0.01;
            $netAmount = (float) $settlementAmount - (float) $deduction;
            if ($netAmount <= 0) {
                throw new \RuntimeException('Invalid net amount');
            }

            $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
            if (!$user) {
                throw new \RuntimeException('User record not found');
            }

            $this->creditWallet($user, (float) $netAmount, $reference, 'Wallet Funding – Automatic Funding');

            if (Schema::hasTable('fundings')) {
                DB::table('fundings')->where('reference', $reference)->update([
                    'fullname' => 'paymentpoint',
                    'funding_type' => 'Automatic Funding',
                ]);
            }
        });

        $this->markSucceeded($event);
    }

    private function processMonnify(PaymentWebhookEvent $event): void
    {
        if (!$event->signature_valid) {
            $this->markIgnored($event, 'Invalid signature');
            return;
        }

        $payload = $event->payload;
        $eventType = (string) ($payload['eventType'] ?? '');
        if ($eventType !== 'SUCCESSFUL_TRANSACTION') {
            $this->markIgnored($event, 'Ignored event');
            return;
        }

        $data = (array) ($payload['eventData'] ?? []);
        $reference = trim((string) ($data['transactionReference'] ?? $data['paymentReference'] ?? ''));
        $currency = Str::upper((string) ($data['currency'] ?? 'NGN'));
        $email = Str::lower((string) ($data['customer']['email'] ?? $data['customerEmailAddress'] ?? ''));

        $amount = null;
        if (isset($data['settlementAmount'])) {
            $amount = (float) $data['settlementAmount'];
        } elseif (isset($data['amountPaid'])) {
            $amount = (float) $data['amountPaid'];
        }
        $amount = $amount !== null ? round((float) $amount, 2) : 0.0;

        if ($reference === '' || $email === '' || $currency !== 'NGN' || $amount <= 0) {
            $this->markIgnored($event, 'Invalid payload');
            return;
        }

        if ($this->alreadyProcessed($reference)) {
            $this->markSucceeded($event);
            return;
        }

        DB::transaction(function () use ($email, $amount, $reference) {
            $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
            if (!$user) {
                throw new \RuntimeException('User not found');
            }

            $this->creditWallet($user, (float) $amount, $reference, 'Wallet Funding – Monnify');

            if (Schema::hasTable('fundings')) {
                DB::table('fundings')->where('reference', $reference)->update([
                    'funding_type' => 'Automatic Funding',
                    'fullname' => 'Monnify',
                ]);
            }
        });

        $this->markSucceeded($event);
    }
}
