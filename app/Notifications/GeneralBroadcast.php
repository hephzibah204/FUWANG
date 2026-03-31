<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Broadcast;

class GeneralBroadcast extends Notification implements ShouldQueue
{
    use Queueable;

    public $broadcast;

    /**
     * Create a new notification instance.
     */
    public function __construct(Broadcast $broadcast)
    {
        $this->broadcast = $broadcast;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject($this->broadcast->subject)
                    ->line(strip_tags($this->broadcast->message))
                    ->action('View Dashboard', url('/dashboard'))
                    ->line('Thank you for using Fuwa.NG!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'broadcast_id' => $this->broadcast->id,
            'subject' => $this->broadcast->subject,
            'message' => $this->broadcast->message,
            'sent_at' => now(),
        ];
    }
}
