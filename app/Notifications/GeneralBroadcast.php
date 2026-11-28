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
     *
     * Broadcasts carry Word-style formatting (owner request 2026-11-28):
     * the sanitised HTML is rendered by the emails.broadcast view.
     * Legacy plain-text messages keep their line breaks instead.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $raw = (string) $this->broadcast->message;
        $contentHtml = \App\Support\RichText::isHtml($raw)
            ? \App\Support\RichText::sanitize($raw)
            : nl2br(e($raw));

        return (new MailMessage)
                    ->subject($this->broadcast->subject)
                    ->view('emails.broadcast', [
                        'subject' => $this->broadcast->subject,
                        'contentHtml' => $contentHtml,
                    ]);
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
            // Renderers show format=html messages as sanitised rich text.
            'format' => 'html',
            'sent_at' => now(),
        ];
    }
}
