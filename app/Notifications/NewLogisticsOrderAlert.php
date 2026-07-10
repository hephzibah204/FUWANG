<?php

namespace App\Notifications;

use App\Models\LogisticsRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewLogisticsOrderAlert extends Notification
{
    use Queueable;

    public function __construct(private LogisticsRequest $shipment)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New delivery order available',
            'message' => 'A new logistics order is ready for assignment.',
            'tracking_id' => $this->shipment->tracking_id,
            'delivery_type' => $this->shipment->delivery_type,
            'recipient_address' => $this->shipment->recipient_address,
            'action_url' => route('admin.operations.logistics'),
        ];
    }
}
