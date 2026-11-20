<?php

namespace App\Jobs;

use App\Models\ApiCenter;
use App\Models\BankDetail;
use App\Models\PaymentIntent;
use App\Models\PaymentWebhookEvent;
use App\Models\User;
use App\Models\VirtualAccount;
use App\Services\WalletService;
use App\Support\DbTable;
use App\Support\PaymentProviderCredentials;
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

        $lock = null;
        try {
            $lockKey = "webhook_process_{$event->provider}_{$event->provider_event_id}_{$event->reference}";
            $lock = Cache::lock($lockKey, 30); // 30s lock
            if (!$lock->get()) {
                // Already being processed elsewhere
                return;
            }
        } catch (\BadMethodCallException|\Throwable $e) {
            // Cache driver does not support atomic locks; proceed with database transaction-level safety
            $lock = null;
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
            if ($lock) {
                try {
                    $lock->release();
                } catch (\Throwable $e) {
                    // Ignore release errors
                }
            }
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
        $current = PaymentWebhookEvent::query()->where('id', $event->id)->first();
        $error = (string) ($current?->processing_error ?? '');
        if (str_contains($error, 'Resolved via existing manual credit')) {
            PaymentWebhookEvent::query()->where('id', $event->id)->update([
                'processing_status' => 'succeeded',
                'processed_at' => now(),
            ]);
            return;
        }

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

    public function findExistingManualCredit(User $user, float $amount, ?PaymentWebhookEvent $event = null): ?object
    {
        if (!$event) {
            return null;
        }

        // 1. Direct match by transaction reference in fundings or transactions
        if (Schema::hasTable('fundings')) {
            $exactFunding = DB::table('fundings')
                ->where('email', $user->email)
                ->where(function ($q) use ($event) {
                    $q->where('reference', $event->reference)
                      ->orWhere('description', 'like', "%{$event->reference}%");
                })
                ->first();
            if ($exactFunding) {
                return (object)[
                    'type' => 'funding_exact_ref',
                    'reference' => $exactFunding->reference,
                    'amount' => (float)$exactFunding->amount,
                    'created_at' => $exactFunding->created_at,
                ];
            }
        }

        if (Schema::hasTable('transactions')) {
            $exactTx = DB::table('transactions')
                ->where('user_email', $user->email)
                ->where(function ($q) use ($event) {
                    $q->where('transaction_id', $event->reference)
                      ->orWhere('order_type', 'like', "%{$event->reference}%");
                })
                ->first();
            if ($exactTx) {
                return (object)[
                    'type' => 'transaction_exact_ref',
                    'reference' => $exactTx->transaction_id,
                    'amount' => (float)($exactTx->balance_after - $exactTx->balance_before),
                    'created_at' => $exactTx->created_at,
                ];
            }
        }

        // 2. Gather already claimed manual credit references so one manual credit does not match multiple webhooks
        $alreadyClaimedRefs = PaymentWebhookEvent::query()
            ->where('id', '!=', $event->id)
            ->whereNotNull('processing_error')
            ->where('processing_error', 'like', '%Resolved via existing manual credit%')
            ->pluck('processing_error')
            ->all();

        // 3. Match by recent manual credit (Admin Credit / Manual Funding) for same user & matching amount
        $minAmount = max(0.01, round($amount * 0.95, 2));
        $maxAmount = round($amount * 1.05 + 65, 2);

        $eventDate = $event->created_at ?? now();
        $startTime = $eventDate->copy()->subHours(72);

        if (Schema::hasTable('fundings')) {
            $fundings = DB::table('fundings')
                ->where('email', $user->email)
                ->where(function ($q) {
                    $q->whereIn('funding_type', ['Admin Credit', 'Manual Funding', 'Manual Credit'])
                      ->orWhere('description', 'like', '%Admin Credit%')
                      ->orWhere('description', 'like', '%Manual%')
                      ->orWhere('description', 'like', '%Reconciliation%');
                })
                ->where('created_at', '>=', $startTime)
                ->whereBetween('amount', [$minAmount, $maxAmount])
                ->orderByDesc('id')
                ->get();

            foreach ($fundings as $f) {
                $isClaimed = false;
                foreach ($alreadyClaimedRefs as $claim) {
                    if (str_contains($claim, (string)$f->reference)) {
                        $isClaimed = true;
                        break;
                    }
                }
                if (!$isClaimed) {
                    return (object)[
                        'type' => 'funding_manual',
                        'reference' => $f->reference,
                        'amount' => (float)$f->amount,
                        'created_at' => $f->created_at,
                    ];
                }
            }
        }

        if (Schema::hasTable('transactions')) {
            $txs = DB::table('transactions')
                ->where('user_email', $user->email)
                ->where(function ($q) {
                    $q->where('order_type', 'like', 'Admin Credit%')
                      ->orWhere('order_type', 'like', 'Manual%')
                      ->orWhere('order_type', 'like', '%Admin Override%');
                })
                ->where('created_at', '>=', $startTime)
                ->orderByDesc('id')
                ->get();

            foreach ($txs as $tx) {
                $txAmt = round((float)($tx->balance_after - $tx->balance_before), 2);
                if ($txAmt >= $minAmount && $txAmt <= $maxAmount) {
                    $isClaimed = false;
                    foreach ($alreadyClaimedRefs as $claim) {
                        if (str_contains($claim, (string)$tx->transaction_id)) {
                            $isClaimed = true;
                            break;
                        }
                    }
                    if (!$isClaimed) {
                        return (object)[
                            'type' => 'transaction_manual',
                            'reference' => $tx->transaction_id,
                            'amount' => $txAmt,
                            'created_at' => $tx->created_at,
                        ];
                    }
                }
            }
        }

        return null;
    }

    private function creditWallet(User $user, float $amount, string $reference, string $label, ?PaymentWebhookEvent $event = null): bool
    {
        if ($this->alreadyProcessed($reference)) {
            return false;
        }

        // Safeguard against double-crediting if user was already credited manually
        if ($event) {
            $existingManual = $this->findExistingManualCredit($user, $amount, $event);
            if ($existingManual) {
                Log::info("Skipping wallet credit for webhook event #{$event->id}: user {$user->email} was already credited manually (Ref: {$existingManual->reference}, Amount: {$existingManual->amount}).");
                PaymentWebhookEvent::query()->where('id', $event->id)->update([
                    'processing_status' => 'succeeded',
                    'processing_error' => "Resolved via existing manual credit (Ref: {$existingManual->reference}, Amount: NGN " . number_format($existingManual->amount, 2) . ")",
                    'processed_at' => now(),
                ]);
                return false;
            }
        }

        $credit = app(WalletService::class)->credit($user, $amount, $label, $reference);
        if (!$credit['ok']) {
            throw new \RuntimeException('Wallet credit was declined.');
        }

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

        return true;
    }

    public function findUserForPayment(
        ?string $email,
        ?string $accountNumber = null,
        ?string $accountReference = null,
        ?string $phone = null
    ): ?User {
        // 1. Primary Priority: Dedicated Virtual Account Number matching
        $rawAcct = trim((string) $accountNumber);
        if ($rawAcct !== '') {
            $cleanAcct = preg_replace('/\D/', '', $rawAcct);
            $acctVariants = array_values(array_unique(array_filter([
                $rawAcct,
                $cleanAcct,
                ltrim($cleanAcct, '0'),
                str_pad($cleanAcct, 10, '0', STR_PAD_LEFT),
            ])));

            if (Schema::hasTable('virtual_accounts')) {
                $va = VirtualAccount::query()->whereIn('account_number', $acctVariants)->first();
                if ($va && $va->user_id) {
                    $user = User::query()->find($va->user_id);
                    if ($user) {
                        return $user;
                    }
                }
            }

            if (Schema::hasTable('bank_details')) {
                $detail = BankDetail::query()
                    ->where(function ($q) use ($acctVariants) {
                        $q->whereIn('psb9', $acctVariants)
                          ->orWhereIn('palmpay', $acctVariants)
                          ->orWhereIn('Wema_account', $acctVariants)
                          ->orWhereIn('Moniepoint_account', $acctVariants)
                          ->orWhereIn('Sterling_account', $acctVariants)
                          ->orWhereIn('GTBank_account', $acctVariants);
                    })
                    ->first();

                if ($detail && !empty($detail->email)) {
                    $user = User::query()->whereRaw('LOWER(email) = ?', [strtolower($detail->email)])->first();
                    if ($user) {
                        return $user;
                    }
                }
            }
        }

        // 2. Secondary Priority: Payment Intent or Account Reference
        $accountReference = trim((string) $accountReference);
        if ($accountReference !== '') {
            if (Schema::hasTable('payment_intents')) {
                $intent = PaymentIntent::query()->where('reference', $accountReference)->first();
                if ($intent && $intent->user_id) {
                    $user = User::query()->find($intent->user_id);
                    if ($user) {
                        return $user;
                    }
                }
            }

            if (Schema::hasTable('virtual_accounts')) {
                $va = VirtualAccount::query()
                    ->where('reference', $accountReference)
                    ->orWhere('provider_account_reference', $accountReference)
                    ->first();
                if ($va && $va->user_id) {
                    $user = User::query()->find($va->user_id);
                    if ($user) {
                        return $user;
                    }
                }
            }

            if (Schema::hasTable('bank_details')) {
                $detail = BankDetail::query()->where('account_reference', $accountReference)->first();
                if ($detail && !empty($detail->email)) {
                    $user = User::query()->whereRaw('LOWER(email) = ?', [strtolower($detail->email)])->first();
                    if ($user) {
                        return $user;
                    }
                }
            }
        }

        // 3. Third Priority: User Email (for direct card checkouts e.g. Paystack / Flutterwave)
        $email = trim((string) $email);
        if ($email !== '') {
            $user = User::query()->whereRaw('LOWER(email) = ?', [strtolower($email)])->first();
            if ($user) {
                return $user;
            }
        }

        // 4. Fourth Priority: User Phone number
        $phone = trim((string) $phone);
        if ($phone !== '') {
            $digits = preg_replace('/\D/', '', $phone);
            if (strlen($digits) >= 10) {
                $tail = substr($digits, -10);
                $user = User::query()
                    ->where('number', $digits)
                    ->orWhere('number', 'like', "%{$tail}")
                    ->first();
                if ($user) {
                    return $user;
                }
            }
        }

        return null;
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

        $payload = (array) $event->payload;
        $evt = (string) ($payload['event'] ?? '');
        if ($evt !== 'charge.success') {
            $this->markIgnored($event, 'Ignored event');
            return;
        }

        $obj = (array) ($payload['data'] ?? []);
        $reference = trim((string) ($obj['reference'] ?? ''));
        $status = Str::lower((string) ($obj['status'] ?? ''));
        $currency = Str::upper((string) ($obj['currency'] ?? ''));
        $amountKobo = (int) ($obj['amount'] ?? 0);
        $email = Str::lower(trim((string) ($obj['customer']['email'] ?? '')));
        $amount = round($amountKobo / 100, 2);

        if ($reference === '' || $status !== 'success' || $currency !== 'NGN' || $amount <= 0) {
            $this->markIgnored($event, 'Invalid payload');
            return;
        }

        if ($this->alreadyProcessed($reference)) {
            $this->markSucceeded($event);
            return;
        }

        $metadata = (array) ($obj['metadata'] ?? []);
        $altRef = trim((string) ($metadata['intent_reference'] ?? $metadata['reference'] ?? $obj['trxref'] ?? ''));
        if ($altRef !== '' && Schema::hasTable('payment_intents')) {
            if (!PaymentIntent::where('reference', $reference)->exists() && PaymentIntent::where('reference', $altRef)->exists()) {
                $reference = $altRef;
            }
        }

        $paymentType = (string) ($metadata['payment_type'] ?? '');
        $isAgentLicense = ($paymentType === 'agent_license') || str_starts_with($reference, 'LIC-');

        if ($isAgentLicense) {
            DB::transaction(function () use ($email, $amount, $reference, $metadata, $obj) {
                $user = $this->findUserForPayment($email, null, $reference);
                if (!$user) {
                    throw new \RuntimeException("User not found for Paystack payment (email: '{$email}')");
                }

                $agentId = $metadata['agent_id'] ?? null;
                $agent = $agentId 
                    ? \App\Models\EnrollmentAgent::find($agentId) 
                    : $user->enrollmentAgent;

                if ($agent) {
                    $agent->update([
                        'license_status' => 'paid',
                        'license_fee_amount' => $amount,
                        'license_fee_paid' => $amount,
                        'license_payment_method' => 'paystack',
                        'license_payment_reference' => $reference,
                        'license_paid_at' => now(),
                        'license_rejection_reason' => null,
                    ]);

                    \App\Models\AgentLicenseTransaction::updateOrCreateSafe(
                        ['reference' => $reference],
                        [
                            'agent_id' => $agent->id,
                            'user_id' => $user->id,
                            'amount' => $amount,
                            'payment_method' => 'paystack',
                            'gateway_reference' => (string) ($obj['id'] ?? $reference),
                            'status' => 'completed',
                            'meta' => $obj,
                        ]
                    );
                }
            });

            $this->markSucceeded($event);
            return;
        }

        DB::transaction(function () use ($email, $amount, $reference, $event) {
            $user = $this->findUserForPayment($email, null, $reference);
            if (!$user) {
                throw new \RuntimeException("User not found for Paystack payment (email: '{$email}')");
            }

            $intent = PaymentIntent::query()->where('reference', $reference)->lockForUpdate()->first();
            if ($intent && $intent->amount_expected !== null && abs((float) $intent->amount_expected - (float) $amount) > 0.01) {
                throw new \RuntimeException('Amount mismatch');
            }

            $this->creditWallet($user, (float) $amount, $reference, 'Wallet Funding – Paystack', $event);
            $this->updateIntent($reference, 'paystack');
        });

        $this->markSucceeded($event);
    }

    private function processFlutterwave(PaymentWebhookEvent $event): void
    {
        $payload = (array) $event->payload;
        $evt = (string) ($payload['event'] ?? '');
        if ($evt !== 'charge.completed') {
            $this->markIgnored($event, 'Ignored event');
            return;
        }

        $obj = (array) ($payload['data'] ?? []);
        $txId = trim((string) ($obj['id'] ?? ''));
        $txRef = trim((string) ($obj['tx_ref'] ?? ''));

        $apiCenter = ApiCenter::first();
        $secret = PaymentProviderCredentials::flutterwave($apiCenter)['secret_key'];
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
        $email = Str::lower(trim((string) ($vd['customer']['email'] ?? '')));
        $reference = trim((string) ($vd['tx_ref'] ?? $txRef ?: $txId));

        if ($status !== 'successful' || $currency !== 'NGN' || $reference === '' || $amount <= 0) {
            $this->markIgnored($event, 'Invalid verification data');
            return;
        }

        if ($this->alreadyProcessed($reference)) {
            $this->markSucceeded($event);
            return;
        }

        DB::transaction(function () use ($email, $amount, $reference, $event) {
            $user = $this->findUserForPayment($email, null, $reference);
            if (!$user) {
                throw new \RuntimeException("User not found for Flutterwave payment (email: '{$email}')");
            }

            $intent = PaymentIntent::query()->where('reference', $reference)->lockForUpdate()->first();
            if ($intent && $intent->amount_expected !== null && abs((float) $intent->amount_expected - (float) $amount) > 0.01) {
                throw new \RuntimeException('Amount mismatch');
            }

            $this->creditWallet($user, (float) $amount, $reference, 'Wallet Funding – Flutterwave', $event);
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

        $payload = (array) $event->payload;
        $reference = trim((string) (
            $payload['transaction']['reference'] 
            ?? $payload['order']['reference'] 
            ?? $payload['reference'] 
            ?? $payload['transaction']['id'] 
            ?? $payload['id'] 
            ?? ''
        ));
        $settlementAmount = (float) (
            $payload['order']['settlement_amount']
            ?? $payload['transaction']['amount']
            ?? $payload['order']['amount']
            ?? $payload['amount']
            ?? 0
        );
        $email = Str::lower(trim((string) (
            $payload['customer']['email'] 
            ?? $payload['email'] 
            ?? $payload['order']['email'] 
            ?? ''
        )));
        $accountNumber = trim((string) (
            $payload['order']['bank_account']
            ?? $payload['order']['account_number']
            ?? $payload['order']['virtual_account_number']
            ?? $payload['virtual_account']['account_number']
            ?? $payload['transaction']['bank_account']
            ?? $payload['transaction']['account_number']
            ?? $payload['account_number']
            ?? $payload['bank_account']
            ?? ''
        ));
        $accountReference = trim((string) (
            $payload['order']['account_reference'] 
            ?? $payload['account_reference'] 
            ?? $payload['virtual_account']['reference'] 
            ?? ''
        ));

        if ($reference === '' || $settlementAmount <= 0) {
            $this->markIgnored($event, 'Invalid payload');
            return;
        }

        if ($this->alreadyProcessed($reference)) {
            $this->markSucceeded($event);
            return;
        }

        DB::transaction(function () use ($email, $accountNumber, $accountReference, $settlementAmount, $reference, $event) {
            $user = $this->findUserForPayment($email, $accountNumber, $accountReference);
            if (!$user) {
                throw new \RuntimeException("User not found for PayVessel payment (email: '{$email}', acct: '{$accountNumber}')");
            }

            $psbAmount = 50.0;
            if (Schema::hasTable('charges')) {
                $val = DB::table('charges')->where('id', 1)->value('psb_amount');
                if ($val !== null) {
                    $psbAmount = (float) $val;
                }
            } elseif (Schema::hasTable('bank_details')) {
                $detail = BankDetail::where('email', $user->email)->first();
                if ($detail && $detail->psb_amount !== null && (float) $detail->psb_amount > 0) {
                    $psbAmount = (float) $detail->psb_amount;
                }
            }

            $netAmount = round($settlementAmount - $psbAmount, 2);
            if ($netAmount <= 0) {
                // If deposit is smaller than deduction, credit settlement amount directly
                $netAmount = round($settlementAmount, 2);
            }

            $credited = $this->creditWallet($user, (float) $netAmount, $reference, 'Wallet Funding – Automatic Funding', $event);

            if ($credited && Schema::hasTable('fundings')) {
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

        $payload = (array) $event->payload;
        $reference = trim((string) (
            $payload['transaction_id'] 
            ?? $payload['reference'] 
            ?? $payload['order_id'] 
            ?? $payload['data']['reference'] 
            ?? $payload['data']['transaction_id'] 
            ?? ''
        ));
        $settlementAmount = (float) (
            $payload['settlement_amount'] 
            ?? $payload['amount'] 
            ?? $payload['data']['settlement_amount'] 
            ?? $payload['data']['amount'] 
            ?? 0
        );
        $email = Str::lower(trim((string) (
            $payload['customer']['email'] 
            ?? $payload['email'] 
            ?? $payload['data']['customer']['email'] 
            ?? ''
        )));
        $accountNumber = trim((string) (
            $payload['account_number']
            ?? $payload['bank_account_number']
            ?? $payload['recipient_account_number']
            ?? $payload['destination_account_number']
            ?? $payload['data']['account_number']
            ?? $payload['order']['account_number']
            ?? ''
        ));
        $phone = trim((string) (
            $payload['customer']['phone'] 
            ?? $payload['phone'] 
            ?? $payload['data']['customer']['phone'] 
            ?? ''
        ));
        $status = (string) ($payload['transaction_status'] ?? $payload['status'] ?? '');

        if ($status !== '' && !in_array(strtolower($status), ['success', 'successful', 'completed'], true)) {
            $this->markIgnored($event, 'Ignored non-success transaction');
            return;
        }

        if ($reference === '' || $settlementAmount <= 0) {
            $this->markIgnored($event, 'Invalid payload');
            return;
        }

        if ($this->alreadyProcessed($reference)) {
            $this->markSucceeded($event);
            return;
        }

        DB::transaction(function () use ($email, $accountNumber, $phone, $settlementAmount, $reference, $event) {
            $user = $this->findUserForPayment($email, $accountNumber, null, $phone);
            if (!$user) {
                throw new \RuntimeException("User not found for PalmPay payment (email: '{$email}', acct: '{$accountNumber}')");
            }

            $deduction = round((float) $settlementAmount * 0.01, 2);
            $netAmount = round((float) $settlementAmount - $deduction, 2);
            if ($netAmount <= 0) {
                $netAmount = round((float) $settlementAmount, 2);
            }

            $credited = $this->creditWallet($user, (float) $netAmount, $reference, 'Wallet Funding – Automatic Funding', $event);

            if ($credited && Schema::hasTable('fundings')) {
                DB::table('fundings')->where('reference', $reference)->update([
                    'fullname' => 'PalmPay',
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

        $payload = (array) $event->payload;
        $eventType = (string) ($payload['eventType'] ?? '');
        if ($eventType !== 'SUCCESSFUL_TRANSACTION') {
            $this->markIgnored($event, 'Ignored event: ' . $eventType);
            return;
        }

        $data = (array) ($payload['eventData'] ?? []);
        $reference = trim((string) ($data['transactionReference'] ?? $data['paymentReference'] ?? ''));
        $currency = Str::upper((string) ($data['currency'] ?? 'NGN'));
        $email = Str::lower(trim((string) ($data['customer']['email'] ?? $data['customerEmailAddress'] ?? '')));
        $accountNumber = trim((string) (
            $data['destinationAccountInformation']['accountNumber']
            ?? $data['accountDetails']['accountNumber']
            ?? $data['destinationAccountNumber']
            ?? $data['accountNumber']
            ?? ''
        ));
        $accountReference = trim((string) ($data['accountReference'] ?? ''));

        $amount = null;
        if (isset($data['settlementAmount'])) {
            $amount = (float) $data['settlementAmount'];
        } elseif (isset($data['amountPaid'])) {
            $amount = (float) $data['amountPaid'];
        }
        $amount = $amount !== null ? round((float) $amount, 2) : 0.0;

        if ($reference === '' || $currency !== 'NGN' || $amount <= 0) {
            $this->markIgnored($event, 'Invalid payload');
            return;
        }

        if ($this->alreadyProcessed($reference)) {
            $this->markSucceeded($event);
            return;
        }

        DB::transaction(function () use ($email, $accountNumber, $accountReference, $amount, $reference, $event) {
            $user = $this->findUserForPayment($email, $accountNumber, $accountReference);
            if (!$user) {
                throw new \RuntimeException("User not found for Monnify payment (email: '{$email}', acct: '{$accountNumber}')");
            }

            $credited = $this->creditWallet($user, (float) $amount, $reference, 'Wallet Funding – Monnify', $event);

            if ($credited && Schema::hasTable('fundings')) {
                DB::table('fundings')->where('reference', $reference)->update([
                    'funding_type' => 'Automatic Funding',
                    'fullname' => 'Monnify',
                ]);
            }
        });

        $this->markSucceeded($event);
    }
}
