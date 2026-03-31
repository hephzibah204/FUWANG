<?php

namespace App\Notifications;

use App\Models\DirectMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DirectMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private DirectMessage $message, private array $channels)
    {
    }

    public function via($notifiable)
    {
        return $this->channels;
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->message->title,
            'message' => $this->message->message,
            'direct_message_id' => $this->message->id,
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject($this->message->title)
            ->line(strip_tags($this->message->message));
    }
}

