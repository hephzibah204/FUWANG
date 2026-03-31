<?php

namespace App\Notifications;

use App\Models\Referral;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReferralRewardIssuedNotification extends Notification
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
        $amount = number_format((float) $this->referral->reward_amount, 2);

        return (new MailMessage)
            ->subject('Referral reward paid')
            ->greeting('Hello ' . ($notifiable->fullname ?? $notifiable->username ?? ''))
            ->line('Your referral reward of ₦' . $amount . ' has been credited to your wallet.')
            ->action('View referrals', route('referrals.index'));
    }
}

