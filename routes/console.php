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

Artisan::command('payments:reprocess-webhooks {--limit=50}', function () {
    $limit = (int) $this->option('limit');
    if (!\Illuminate\Support\Facades\Schema::hasTable('payment_webhook_events')) {
        $this->error('payment_webhook_events table does not exist.');
        return 1;
    }

    $events = \App\Models\PaymentWebhookEvent::whereIn('processing_status', ['pending', 'failed'])
        ->limit($limit)
        ->get();

    if ($events->isEmpty()) {
        $this->info('No pending or failed webhook events to process.');
        return 0;
    }

    $this->info("Found {$events->count()} events to process...");
    $success = 0;
    $failed = 0;

    foreach ($events as $event) {
        $this->line("Processing event #{$event->id} ({$event->provider} - ref: {$event->reference})...");
        try {
            \App\Jobs\ProcessPaymentWebhookEvent::dispatchSync($event->id);
            $event->refresh();
            if ($event->processing_status === 'succeeded') {
                $this->info("  -> Event #{$event->id} succeeded.");
                $success++;
            } else {
                $this->warn("  -> Event #{$event->id} status: {$event->processing_status} ({$event->processing_error})");
            }
        } catch (\Throwable $e) {
            $this->error("  -> Event #{$event->id} error: " . $e->getMessage());
            $failed++;
        }
    }

    $this->info("Completed. Succeeded: {$success}, Errors: {$failed}");
    return 0;
})->purpose('Reprocess pending or failed payment webhook events');

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
