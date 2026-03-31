<?php

namespace App\Notifications;

use App\Models\Referral;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReferralRegisteredNotification extends Notification
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
        $name = $this->referral->referred?->fullname ?? ($this->referral->referred?->username ?? 'A new user');

        return (new MailMessage)
            ->subject('New referral signup')
            ->greeting('Hello ' . ($notifiable->fullname ?? $notifiable->username ?? ''))
            ->line($name . ' just registered using your referral code.')
            ->action('View referrals', route('referrals.index'));
    }
}

