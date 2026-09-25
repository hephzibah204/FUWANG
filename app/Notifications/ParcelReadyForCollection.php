<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ParcelReadyForCollection extends Notification
{
    use Queueable;

    public $parcel;
    public $shop;

    /**
     * Create a new notification instance.
     */
    public function __construct($parcel, $shop)
    {
        $this->parcel = $parcel;
        $this->shop = $shop;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail']; // add 'nexmo' or 'twilio' for SMS later
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Your Parcel is Ready for Pickup!')
                    ->greeting('Hello,')
                    ->line('Your parcel (Tracking: ' . $this->parcel->tracking_number . ') is now ready for collection.')
                    ->line('Pickup Location: ' . $this->shop->name)
                    ->line('Address: ' . $this->shop->address . ', ' . $this->shop->city)
                    ->line('Please remember to bring a valid ID matching the recipient name.')
                    ->action('Track Package', url('/dashboard'))
                    ->line('Thank you for using Fuwa.NG logistics network!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
