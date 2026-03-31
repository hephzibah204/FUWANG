<?php

namespace App\Jobs\Campaigns;

use App\Models\CustomApi;
use App\Models\SmsCampaign;
use App\Models\SmsCampaignRecipient;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendSmsCampaignRecipientJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function __construct(public int $campaignId, public int $recipientId)
    {
        $this->onQueue('sms-campaigns');
    }

    public function middleware(): array
    {
        return [
            new RateLimited('sms-campaigns'),
        ];
    }

    public function backoff(): array
    {
        return [10, 60, 180, 600, 1800];
    }

    public function handle(SmsService $sms): void
    {
        $recipient = SmsCampaignRecipient::query()->where('id', $this->recipientId)->first();
        if (!$recipient || $recipient->sms_campaign_id !== $this->campaignId) {
            return;
        }
        if ($recipient->status === 'sent') {
            return;
        }

        $campaign = SmsCampaign::find($this->campaignId);
        if (!$campaign) {
            return;
        }

        $provider = null;
        if ($campaign->custom_api_id) {
            $provider = CustomApi::find($campaign->custom_api_id);
        }
        if (!$provider) {
            throw new \RuntimeException('SMS provider not configured.');
        }

        try {
            $resp = $sms->send($provider, (string) $recipient->phone, (string) $campaign->message, $campaign->sender_id);
            if (!$resp['ok']) {
                throw new \RuntimeException('Provider error: ' . substr((string) ($resp['body'] ?? ''), 0, 500));
            }

            SmsCampaignRecipient::where('id', $recipient->id)->update([
                'status' => 'sent',
                'attempts' => $this->attempts(),
                'provider_response' => is_string($resp['body'] ?? null) ? substr($resp['body'], 0, 5000) : null,
                'sent_at' => now(),
                'updated_at' => now(),
            ]);
            SmsCampaign::where('id', $campaign->id)->increment('delivered_count');
        } catch (Throwable $e) {
            SmsCampaignRecipient::where('id', $recipient->id)->update([
                'attempts' => $this->attempts(),
                'provider_response' => substr((string) $e->getMessage(), 0, 5000),
                'updated_at' => now(),
            ]);
            throw $e;
        }
    }

    public function failed(Throwable $e): void
    {
        SmsCampaignRecipient::where('id', $this->recipientId)->where('status', '!=', 'sent')->update([
            'status' => 'failed',
            'attempts' => $this->attempts(),
            'provider_response' => substr((string) $e->getMessage(), 0, 5000),
            'sent_at' => now(),
            'updated_at' => now(),
        ]);
        SmsCampaign::where('id', $this->campaignId)->increment('failed_count');
    }
}

