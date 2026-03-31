<?php

namespace App\Notifications;

use App\Models\Referral;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReferralFundedNotification extends Notification
{
    use Queueable;

    public function __construct(public Referral $referral)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $name = $this->referral->referred?->fullname ?? ($this->referral->referred?->username ?? 'Your referral');

        return (new MailMessage)
            ->subject('Referral funded')
            ->greeting('Hello ' . ($notifiable->fullname ?? $notifiable->username ?? ''))
            ->line($name . ' funded their wallet. Your referral status has been updated.')
            ->action('View referrals', route('referrals.index'));
    }
}

