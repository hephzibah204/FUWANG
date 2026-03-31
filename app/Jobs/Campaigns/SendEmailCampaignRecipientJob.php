<?php

namespace App\Jobs\Campaigns;

use App\Mail\EmailCampaignMail;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendEmailCampaignRecipientJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function __construct(public int $campaignId, public int $recipientId)
    {
        $this->onQueue('email-campaigns');
    }

    public function middleware(): array
    {
        return [
            new RateLimited('email-campaigns'),
        ];
    }

    public function backoff(): array
    {
        return [30, 120, 300, 900, 1800];
    }

    public function handle(): void
    {
        $recipient = EmailCampaignRecipient::query()->where('id', $this->recipientId)->first();
        if (!$recipient || $recipient->email_campaign_id !== $this->campaignId) {
            return;
        }
        if ($recipient->status === 'sent') {
            return;
        }

        $campaign = EmailCampaign::find($this->campaignId);
        if (!$campaign) {
            return;
        }

        $user = null;
        if ($recipient->user_id) {
            $user = User::query()->where('id', $recipient->user_id)->first(['id', 'fullname', 'email']);
        }
        if (!$user) {
            $user = User::query()->where('email', $recipient->email)->first(['id', 'fullname', 'email']);
        }

        $name = $user?->fullname ?? '';
        $html = str_replace(['{{name}}', '{{ name }}'], e($name), $campaign->html);

        try {
            Mail::to($recipient->email)->send(new EmailCampaignMail($campaign->subject, $html));

            EmailCampaignRecipient::where('id', $recipient->id)->update([
                'status' => 'sent',
                'attempts' => $this->attempts(),
                'sent_at' => now(),
                'updated_at' => now(),
            ]);
            EmailCampaign::where('id', $campaign->id)->increment('delivered_count');
        } catch (Throwable $e) {
            EmailCampaignRecipient::where('id', $recipient->id)->update([
                'attempts' => $this->attempts(),
                'provider_response' => substr((string) $e->getMessage(), 0, 5000),
                'updated_at' => now(),
            ]);
            throw $e;
        }
    }

    public function failed(Throwable $e): void
    {
        EmailCampaignRecipient::where('id', $this->recipientId)->where('status', '!=', 'sent')->update([
            'status' => 'failed',
            'attempts' => $this->attempts(),
            'provider_response' => substr((string) $e->getMessage(), 0, 5000),
            'sent_at' => now(),
            'updated_at' => now(),
        ]);
        EmailCampaign::where('id', $this->campaignId)->increment('failed_count');
    }
}

