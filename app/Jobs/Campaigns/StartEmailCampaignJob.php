<?php

namespace App\Jobs\Campaigns;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\User;
use App\Services\UserTargeting\UserSegment;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Throwable;

class StartEmailCampaignJob implements ShouldQueue
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
        if (in_array($campaign->status, ['sending', 'sent'], true)) {
            return;
        }

        $campaign->update([
            'status' => 'sending',
            'recipient_count' => 0,
            'delivered_count' => 0,
            'failed_count' => 0,
            'sent_at' => null,
        ]);

        EmailCampaignRecipient::query()->where('email_campaign_id', $campaign->id)->delete();

        $users = $this->buildAudienceQuery($campaign)->whereNotNull('email');

        $inserted = 0;
        $users->select(['id', 'email'])->chunkById(1000, function ($chunk) use ($campaign, &$inserted) {
            $rows = [];
            foreach ($chunk as $u) {
                $rows[] = [
                    'email_campaign_id' => $campaign->id,
                    'user_id' => $u->id,
                    'email' => $u->email,
                    'status' => 'pending',
                    'attempts' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (!empty($rows)) {
                EmailCampaignRecipient::query()->insertOrIgnore($rows);
                $inserted += count($rows);
            }
        });

        $campaign->update(['recipient_count' => $inserted]);

        $batch = Bus::batch([])
            ->name('Email Campaign #' . $campaign->id)
            ->onQueue('email-campaigns')
            ->then(function (Batch $b) use ($campaign) {
                EmailCampaign::where('id', $campaign->id)->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
            })
            ->catch(function (Batch $b, Throwable $e) use ($campaign) {
                EmailCampaign::where('id', $campaign->id)->update([
                    'status' => 'failed',
                ]);
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

    private function buildAudienceQuery(EmailCampaign $campaign): Builder
    {
        $users = User::query();

        if ($campaign->audience_type === 'emails') {
            $users->whereIn('email', $campaign->audience ?? []);
        } elseif ($campaign->audience_type === 'user_ids') {
            $ids = array_map('intval', $campaign->audience ?? []);
            $users->whereIn('id', $ids);
        } elseif ($campaign->audience_type === 'segment' && is_array($campaign->audience)) {
            UserSegment::apply($users, $campaign->audience);
        }

        return $users;
    }
}

