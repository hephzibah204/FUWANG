<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:aggregate-metrics')->everyTenMinutes();
Schedule::command('agent:retry-pending-nin')->everyFifteenMinutes();
Schedule::command('auction:process-status')->everyMinute();
Schedule::command('bvn:poll-retrievals')->everyFifteenMinutes();
Schedule::command('vtu:reconcile')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('payments:reprocess-webhooks --recent-only')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('payments:reconcile-intents --credit')->hourly()->withoutOverlapping();
Schedule::command('system:health-check')->daily();

Artisan::command('payments:reprocess-webhooks {--limit=50} {--all : Reprocess all pending and failed webhook events} {--dry-run : Simulate without changing wallet balances or database} {--recent-only : Only process webhooks received in the last 6 hours}', function () {
    $limit = (int) $this->option('limit');
    $all = (bool) $this->option('all');
    $dryRun = (bool) $this->option('dry-run');
    $recentOnly = (bool) $this->option('recent-only');

    if (!\Illuminate\Support\Facades\Schema::hasTable('payment_webhook_events')) {
        $this->error('payment_webhook_events table does not exist.');
        return 1;
    }

    $query = \App\Models\PaymentWebhookEvent::whereIn('processing_status', ['pending', 'failed'])
        ->orderBy('id', 'asc');

    if ($recentOnly) {
        $query->where('created_at', '>=', now()->subHours(6));
    }

    if (!$all && $limit > 0) {
        $query->limit($limit);
    }

    $events = $query->get();

    if ($events->isEmpty()) {
        $this->info('No pending or failed webhook events to process.');
        return 0;
    }

    if ($dryRun) {
        $this->warn("=== DRY-RUN MODE: No wallets will be credited, no events modified ===");
    }

    $this->info("Found {$events->count()} events to process...");
    $credited = 0;
    $skippedManual = 0;
    $failed = 0;

    $jobHelper = new \App\Jobs\ProcessPaymentWebhookEvent(0);

    foreach ($events as $event) {
        $payload = (array) $event->payload;
        $ref = $event->reference ?: 'no-ref';
        $amount = (float) ($event->amount ?? 0);
        if ($amount <= 0) {
            $amount = (float) (
                $payload['order']['settlement_amount'] 
                ?? $payload['settlement_amount'] 
                ?? $payload['order']['amount'] 
                ?? $payload['amount'] 
                ?? 0
            );
        }

        // Extract user info
        $email = $event->email ?: ($payload['customer']['email'] ?? $payload['email'] ?? null);
        $acct = $payload['order']['account_number'] 
            ?? $payload['order']['bank_account'] 
            ?? $payload['account_number'] 
            ?? $payload['bank_account_number'] 
            ?? null;
        $acctRef = $payload['order']['account_reference'] ?? $payload['accountReference'] ?? null;
        $phone = $payload['customer']['phone'] ?? $payload['phone'] ?? null;

        $user = $jobHelper->findUserForPayment($email, $acct, $acctRef, $phone);
        $userStr = $user ? "{$user->email} (ID: {$user->id})" : "NOT FOUND ({$email}/{$acct})";

        // Check if user was already credited manually
        $manualCredit = ($user && $amount > 0) ? $jobHelper->findExistingManualCredit($user, $amount, $event) : null;

        if ($dryRun) {
            if ($manualCredit) {
                $this->comment("  [EVENT #{$event->id}] {$event->provider} | {$userStr} | NGN {$amount}");
                $this->info("    -> WOULD SKIP (Already credited manually via ref: {$manualCredit->reference}, amount: NGN {$manualCredit->amount})");
                $skippedManual++;
            } elseif (!$user) {
                $this->error("  [EVENT #{$event->id}] {$event->provider} | {$userStr} | NGN {$amount}");
                $this->error("    -> WOULD FAIL (User not found)");
                $failed++;
            } else {
                $this->line("  [EVENT #{$event->id}] {$event->provider} | {$userStr} | NGN {$amount}");
                $this->info("    -> WOULD CREDIT to wallet");
                $credited++;
            }
            continue;
        }

        $this->line("Processing event #{$event->id} ({$event->provider} - ref: {$ref})...");
        try {
            \App\Jobs\ProcessPaymentWebhookEvent::dispatchSync($event->id);
            $event->refresh();
            if ($event->processing_status === 'succeeded') {
                if (str_contains((string) $event->processing_error, 'Resolved via existing manual credit')) {
                    $this->info("  -> Event #{$event->id} resolved via existing manual credit (wallet credit skipped).");
                    $skippedManual++;
                } else {
                    $this->info("  -> Event #{$event->id} succeeded & credited to wallet.");
                    $credited++;
                }
            } else {
                $this->warn("  -> Event #{$event->id} status: {$event->processing_status} ({$event->processing_error})");
                $failed++;
            }
        } catch (\Throwable $e) {
            $this->error("  -> Event #{$event->id} error: " . $e->getMessage());
            $failed++;
        }
    }

    $mode = $dryRun ? 'Dry run completed.' : 'Reprocessing completed.';
    $this->info("{$mode} Credited: {$credited}, Skipped (Already Manually Credited): {$skippedManual}, Errors/Unresolved: {$failed}");
    return 0;
})->purpose('Reprocess pending or failed payment webhook events with manual credit protection');

Artisan::command('payments:reconcile-manual-credits {--limit=200} {--dry-run : Simulate reconciliation without modifying database}', function () {
    $limit = (int) $this->option('limit');
    $dryRun = (bool) $this->option('dry-run');

    if (!\Illuminate\Support\Facades\Schema::hasTable('payment_webhook_events')) {
        $this->error('payment_webhook_events table does not exist.');
        return 1;
    }

    $events = \App\Models\PaymentWebhookEvent::whereIn('processing_status', ['pending', 'failed'])
        ->orderBy('id', 'asc')
        ->limit($limit)
        ->get();

    if ($events->isEmpty()) {
        $this->info('No pending or failed webhook events to reconcile.');
        return 0;
    }

    $this->info("Scanning {$events->count()} pending/failed webhook events for manual credit matches...");
    $jobHelper = new \App\Jobs\ProcessPaymentWebhookEvent(0);
    $matched = 0;
    $unmatched = 0;

    foreach ($events as $event) {
        $payload = (array) $event->payload;
        $amount = (float) ($event->amount ?? 0);
        if ($amount <= 0) {
            $amount = (float) (
                $payload['order']['settlement_amount'] 
                ?? $payload['settlement_amount'] 
                ?? $payload['order']['amount'] 
                ?? $payload['amount'] 
                ?? 0
            );
        }

        $email = $event->email ?: ($payload['customer']['email'] ?? $payload['email'] ?? null);
        $acct = $payload['order']['account_number'] 
            ?? $payload['order']['bank_account'] 
            ?? $payload['account_number'] 
            ?? $payload['bank_account_number'] 
            ?? null;
        $acctRef = $payload['order']['account_reference'] ?? $payload['accountReference'] ?? null;
        $phone = $payload['customer']['phone'] ?? $payload['phone'] ?? null;

        $user = $jobHelper->findUserForPayment($email, $acct, $acctRef, $phone);
        if (!$user) {
            $unmatched++;
            continue;
        }

        $manualCredit = $jobHelper->findExistingManualCredit($user, $amount, $event);
        if ($manualCredit) {
            $matched++;
            $this->info("MATCH FOUND: Event #{$event->id} ({$event->provider} - {$event->reference})");
            $this->line("  -> User: {$user->email} | Webhook Amount: NGN {$amount}");
            $this->line("  -> Manual Credit: Ref: {$manualCredit->reference} | Amount: NGN {$manualCredit->amount} | Date: {$manualCredit->created_at}");

            if (!$dryRun) {
                $event->update([
                    'processing_status' => 'succeeded',
                    'processing_error' => "Resolved via existing manual credit (Ref: {$manualCredit->reference}, Amount: NGN " . number_format($manualCredit->amount, 2) . ")",
                    'processed_at' => now(),
                ]);
                $this->info("  -> Event #{$event->id} marked as SUCCEEDED (manual credit linked).");
            } else {
                $this->comment("  -> [DRY-RUN] Would mark Event #{$event->id} as SUCCEEDED without crediting wallet.");
            }
        } else {
            $unmatched++;
        }
    }

    $this->info("Scan completed. Matched manual credits: {$matched}, Unmatched (Safe to reprocess or investigate): {$unmatched}");
    return 0;
})->purpose('Scan and reconcile pending webhooks against manual admin wallet credits');

Artisan::command('payments:mark-manually-credited {identifier : Event ID, Webhook Reference, or User Email} {--note=Manually credited by admin}', function () {
    $identifier = trim((string) $this->argument('identifier'));
    $note = (string) $this->option('note');

    if (!\Illuminate\Support\Facades\Schema::hasTable('payment_webhook_events')) {
        $this->error('payment_webhook_events table does not exist.');
        return 1;
    }

    $query = \App\Models\PaymentWebhookEvent::query();

    if (is_numeric($identifier)) {
        $query->where('id', (int) $identifier);
    } elseif (str_contains($identifier, '@')) {
        $query->where('email', $identifier)
              ->whereIn('processing_status', ['pending', 'failed']);
    } else {
        $query->where('reference', $identifier);
    }

    $events = $query->get();

    if ($events->isEmpty()) {
        $this->error("No webhook events found matching '{$identifier}'.");
        return 1;
    }

    $count = 0;
    foreach ($events as $event) {
        $event->update([
            'processing_status' => 'succeeded',
            'processing_error' => "Resolved via manual credit: {$note}",
            'processed_at' => now(),
        ]);
        $this->info("Event #{$event->id} ({$event->provider} - Ref: {$event->reference}) marked as SUCCEEDED (manual credit recorded).");
        $count++;
    }

    $this->info("Updated {$count} webhook event(s). They will not be credited again.");
    return 0;
})->purpose('Mark one or more webhook events as manually credited so they are never double-credited');

Artisan::command('payments:credit-user {email} {amount} {--reference=} {--description=Reconciliation Funding}', function () {
    $email = trim($this->argument('email'));
    $amount = (float) $this->argument('amount');
    $reference = trim((string) $this->option('reference')) ?: ('MANUAL-' . strtoupper(\Illuminate\Support\Str::random(10)));
    $description = (string) $this->option('description');

    $user = \App\Models\User::whereRaw('LOWER(email) = ?', [strtolower($email)])->first();
    if (!$user) {
        $this->error("User not found for email: {$email}");
        return 1;
    }

    if ($amount <= 0) {
        $this->error('Amount must be greater than 0.');
        return 1;
    }

    $walletService = app(\App\Services\WalletService::class);
    $res = $walletService->credit($user, $amount, 'Wallet Funding – ' . $description, $reference);

    if (\Illuminate\Support\Facades\Schema::hasTable('fundings')) {
        \Illuminate\Support\Facades\DB::table('fundings')->insert([
            'email' => $user->email,
            'amount' => $amount,
            'reference' => $reference,
            'description' => $description,
            'funding_type' => 'Manual Funding',
            'fullname' => $user->fullname ?? $user->username ?? $user->email,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $this->info("Successfully credited {$user->email} with NGN {$amount}!");
    $this->info("Reference: {$reference}");
    $this->info("Old Balance: NGN {$res['oldBalance']} | New Balance: NGN {$res['newBalance']}");
    return 0;
})->purpose('Manually credit a user wallet and log transaction and funding history');
