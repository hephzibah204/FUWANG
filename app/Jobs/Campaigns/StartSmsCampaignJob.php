<?php

namespace App\Jobs\Campaigns;

use App\Models\CustomApi;
use App\Models\SmsCampaign;
use App\Models\SmsCampaignRecipient;
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

class StartSmsCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $campaignId)
    {
        $this->onQueue('campaigns');
    }

    public function handle(): void
    {
        $campaign = SmsCampaign::find($this->campaignId);
        if (!$campaign) {
            return;
        }
        if (in_array($campaign->status, ['sending', 'sent'], true)) {
            return;
        }

        $provider = null;
        if ($campaign->custom_api_id) {
            $provider = CustomApi::find($campaign->custom_api_id);
        }
        if (!$provider) {
            $provider = CustomApi::query()
                ->where('service_type', 'sms_gateway')
                ->where('status', 1)
                ->orderBy('priority')
                ->first();
        }
        if (!$provider) {
            $campaign->update(['status' => 'failed']);
            return;
        }

        $campaign->update([
            'status' => 'sending',
            'custom_api_id' => $provider->id,
            'recipient_count' => 0,
            'delivered_count' => 0,
            'failed_count' => 0,
            'sent_at' => null,
        ]);

        SmsCampaignRecipient::query()->where('sms_campaign_id', $campaign->id)->delete();

        $users = $this->buildAudienceQuery($campaign)->whereNotNull('number');

        $inserted = 0;
        $users->select(['id', 'number'])->chunkById(1000, function ($chunk) use ($campaign, &$inserted) {
            $rows = [];
            foreach ($chunk as $u) {
                $rows[] = [
                    'sms_campaign_id' => $campaign->id,
                    'user_id' => $u->id,
                    'phone' => $u->number,
                    'status' => 'pending',
                    'attempts' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (!empty($rows)) {
                SmsCampaignRecipient::query()->insert($rows);
                $inserted += count($rows);
            }
        });

        $campaign->update(['recipient_count' => $inserted]);

        $batch = Bus::batch([])
            ->name('SMS Campaign #' . $campaign->id)
            ->onQueue('sms-campaigns')
            ->then(function (Batch $b) use ($campaign) {
                SmsCampaign::where('id', $campaign->id)->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
            })
            ->catch(function (Batch $b, Throwable $e) use ($campaign) {
                SmsCampaign::where('id', $campaign->id)->update([
                    'status' => 'failed',
                ]);
            })
            ->dispatch();

        $campaign->update(['batch_id' => $batch->id]);

        SmsCampaignRecipient::query()
            ->where('sms_campaign_id', $campaign->id)
            ->where('status', 'pending')
            ->orderBy('id')
            ->chunkById(500, function ($recipients) use ($batch, $campaign) {
                $jobs = [];
                foreach ($recipients as $r) {
                    $jobs[] = new SendSmsCampaignRecipientJob($campaign->id, $r->id);
                }
                if (!empty($jobs)) {
                    $batch->add($jobs);
                }
            });
    }

    private function buildAudienceQuery(SmsCampaign $campaign): Builder
    {
        $users = User::query();

        if ($campaign->audience_type === 'phones') {
            $phones = array_values(array_unique(array_filter(array_map('strval', $campaign->audience ?? []))));
            $users->whereIn('number', $phones);
        } elseif ($campaign->audience_type === 'user_ids') {
            $ids = array_map('intval', $campaign->audience ?? []);
            $users->whereIn('id', $ids);
        } elseif ($campaign->audience_type === 'segment' && is_array($campaign->audience)) {
            UserSegment::apply($users, $campaign->audience);
        }

        return $users;
    }
}

