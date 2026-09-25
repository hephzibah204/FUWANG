<?php

namespace App\Services\Parcels\Adapters;

use App\Models\LogisticsRequest;
use App\Services\Parcels\Contracts\CourierAdapterInterface;
use Illuminate\Support\Facades\Log;

class FuwaPostAdapter implements CourierAdapterInterface
{
    public function validateTrackingNumber(string $trackingNumber): ?array
    {
        $logisticsRequest = LogisticsRequest::where('tracking_id', $trackingNumber)->first();

        if (!$logisticsRequest) {
            return null;
        }

        if (in_array($logisticsRequest->status, ['delivered', 'cancelled', 'rejected'])) {
            return null;
        }

        return [
            'tracking_number' => $logisticsRequest->tracking_id,
            'status' => $logisticsRequest->status,
            'weight' => $logisticsRequest->weight,
            'price' => $logisticsRequest->amount,
            'courier_name' => 'FuwaPost',
        ];
    }

    public function fetchParcelDetails(string $trackingNumber): array
    {
        $logisticsRequest = LogisticsRequest::where('tracking_id', $trackingNumber)->first();

        if (!$logisticsRequest) {
            return [
                'sender' => null,
                'receiver' => null,
            ];
        }

        return [
            'sender' => [
                'name' => $logisticsRequest->sender_name,
                'address' => $logisticsRequest->sender_address,
                'state' => $logisticsRequest->sender_state,
                'city' => $logisticsRequest->sender_city,
            ],
            'receiver' => [
                'name' => $logisticsRequest->recipient_name,
                'address' => $logisticsRequest->recipient_address,
                'phone' => $logisticsRequest->recipient_phone,
                'email' => $logisticsRequest->recipient_email,
                'state' => $logisticsRequest->recipient_state,
                'city' => $logisticsRequest->recipient_city,
            ]
        ];
    }

    public function updateParcelStatus(string $trackingNumber, string $status, array $metadata = []): bool
    {
        $logisticsRequest = LogisticsRequest::where('tracking_id', $trackingNumber)->first();

        if (!$logisticsRequest) {
            return false;
        }

        // Map Parcels status to FuwaPost internal status
        $fuwaStatus = $this->mapStatus($status);
        if ($fuwaStatus) {
            $logisticsRequest->status = $fuwaStatus;
            $logisticsRequest->last_status_updated_at = now();
            $logisticsRequest->save();
            
            Log::info("FuwaPostAdapter synced status for {$trackingNumber} to {$fuwaStatus}");
            return true;
        }

        return false;
    }

    /**
     * Map the generic Parcels status to internal FuwaPost LogisticsRequest status
     */
    private function mapStatus(string $parcelStatus): ?string
    {
        $map = [
            'customer_dropped_off' => 'awaiting_pickup',
            'driver_collected' => 'in_transit',
            'driver_dropped_off' => 'ready_for_collection',
            'customer_collected' => 'delivered',
            'rejected' => 'rejected',
            'damaged' => 'exception',
        ];

        return $map[$parcelStatus] ?? null;
    }
}
