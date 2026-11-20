<?php

namespace App\Console\Commands;

use App\Jobs\ProcessPaymentWebhookEvent;
use App\Models\PaymentWebhookEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class ReprocessWebhooksCommand extends Command
{
    protected $signature = 'payments:reprocess-webhooks {--limit=100} {--provider=} {--reference=} {--all}';
    protected $description = 'Reprocess pending and failed payment webhook events safely';

    public function handle(): int
    {
        if (!Schema::hasTable('payment_webhook_events')) {
            $this->error('payment_webhook_events table does not exist.');
            return self::FAILURE;
        }

        $limit = max(1, min(500, (int) $this->option('limit')));
        $provider = $this->option('provider');
        $reference = $this->option('reference');
        $all = (bool) $this->option('all');

        $query = PaymentWebhookEvent::query();

        if ($all) {
            $query->whereIn('processing_status', ['pending', 'failed']);
        } elseif ($reference) {
            $query->where('reference', $reference);
        } else {
            $query->whereIn('processing_status', ['pending', 'failed']);
        }

        if ($provider) {
            $query->where('provider', $provider);
        }

        $events = $query->orderBy('id', 'asc')->limit($limit)->get();

        if ($events->isEmpty()) {
            $this->info('No pending or failed webhook events found.');
            return self::SUCCESS;
        }

        $this->info("Found {$events->count()} webhook event(s) to process.");

        $credited = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($events as $event) {
            try {
                ProcessPaymentWebhookEvent::dispatchSync($event->id);
                $event->refresh();

                if ($event->processing_status === 'succeeded') {
                    if (str_contains((string) $event->processing_error, 'Resolved via existing manual credit')) {
                        $skipped++;
                        $this->line("[SKIPPED - ALREADY CREDITED] Event #{$event->id} ({$event->provider}): Ref {$event->reference}");
                    } else {
                        $credited++;
                        $this->info("[CREDITED] Event #{$event->id} ({$event->provider}): Ref {$event->reference} - NGN {$event->amount}");
                    }
                } elseif ($event->processing_status === 'ignored') {
                    $skipped++;
                    $this->line("[IGNORED] Event #{$event->id} ({$event->provider}): {$event->processing_error}");
                } else {
                    $failed++;
                    $this->warn("[FAILED] Event #{$event->id} ({$event->provider}): Ref {$event->reference} - Error: {$event->processing_error}");
                }
            } catch (\Throwable $e) {
                $failed++;
                $this->error("[ERROR] Event #{$event->id} exception: " . $e->getMessage());
            }
        }

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Processed', $events->count()],
                ['Successfully Credited', $credited],
                ['Skipped / Duplicate', $skipped],
                ['Failed', $failed],
            ]
        );

        return self::SUCCESS;
    }
}
