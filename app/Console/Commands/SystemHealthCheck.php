<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SystemHealthCheck extends Command
{
    protected $signature = 'system:health-check';

    protected $description = 'Payment/queue health tripwire. Prints (a) queued jobs count, (b) payment webhook events received in the last 24h, (c) payment intents still pending after 1 hour. Logs a warning when jobs > 500, or when stale pending intents > 0 while no webhook arrived in 24h (the signature of a broken webhook).';

    public function handle(): int
    {
        $jobsCount = Schema::hasTable('jobs') ? DB::table('jobs')->count() : 0;
        $webhooks24h = Schema::hasTable('payment_webhook_events')
            ? DB::table('payment_webhook_events')->where('created_at', '>=', now()->subHours(24))->count()
            : 0;
        $stalePending = Schema::hasTable('payment_intents')
            ? DB::table('payment_intents')->where('status', 'pending')->where('created_at', '<=', now()->subHours(1))->count()
            : 0;

        $this->info("Queued jobs: {$jobsCount}");
        $this->info("Payment webhook events (last 24h): {$webhooks24h}");
        $this->info("Pending payment intents older than 1 hour: {$stalePending}");

        if ($jobsCount > 500) {
            Log::warning("Health check: job queue backlog is {$jobsCount} (> 500). Queue worker / scheduler cron may not be running.");
            $this->warn('WARNING: job queue backlog above 500 — queue worker may not be running.');
        }

        if ($stalePending > 0 && $webhooks24h === 0) {
            Log::warning("Health check: {$stalePending} payment intent(s) pending > 1h and zero webhook events in the last 24h. Paystack webhook delivery may be broken.");
            $this->warn('WARNING: stale pending intents with zero webhooks in 24h — webhook delivery may be broken.');
        }

        return self::SUCCESS;
    }
}
