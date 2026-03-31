<?php

namespace App\Jobs\Campaigns;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Throwable;

class RetryEmailCampaignFailedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $campaignId)
    {
        $this->onQueue('campaigns');
    }

    public function handle(): void
    {
        $campaign = EmailCampaign::find($this->campaignId);
        if (!$campaign) {
            return;
        }
        if (in_array($campaign->status, ['queued', 'sending'], true)) {
            return;
        }

        EmailCampaignRecipient::query()
            ->where('email_campaign_id', $campaign->id)
            ->where('status', 'failed')
            ->update(['status' => 'pending', 'updated_at' => now()]);

        $pendingCount = EmailCampaignRecipient::query()
            ->where('email_campaign_id', $campaign->id)
            ->where('status', 'pending')
            ->count();

        if ($pendingCount <= 0) {
            return;
        }

        $campaign->update(['status' => 'sending']);

        $batch = Bus::batch([])
            ->name('Email Campaign Retry #' . $campaign->id)
            ->onQueue('email-campaigns')
            ->then(function (Batch $b) use ($campaign) {
                EmailCampaign::where('id', $campaign->id)->update([
                    'status' => 'sent',
                    'sent_at' => $campaign->sent_at ?: now(),
                ]);
            })
            ->catch(function (Batch $b, Throwable $e) use ($campaign) {
                EmailCampaign::where('id', $campaign->id)->update(['status' => 'failed']);
            })
            ->dispatch();

        $campaign->update(['batch_id' => $batch->id]);

        EmailCampaignRecipient::query()
            ->where('email_campaign_id', $campaign->id)
            ->where('status', 'pending')
            ->orderBy('id')
            ->chunkById(500, function ($recipients) use ($batch, $campaign) {
                $jobs = [];
                foreach ($recipients as $r) {
                    $jobs[] = new SendEmailCampaignRecipientJob($campaign->id, $r->id);
                }
                if (!empty($jobs)) {
                    $batch->add($jobs);
                }
            });
    }
}

