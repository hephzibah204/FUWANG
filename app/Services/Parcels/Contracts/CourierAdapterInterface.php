<?php

namespace App\Services\Parcels\Contracts;

interface CourierAdapterInterface
{
    /**
     * Validate if a tracking number exists and is valid for this courier.
     * @return array|null Returns parcel metadata if valid, null otherwise.
     */
    public function validateTrackingNumber(string $trackingNumber): ?array;

    /**
     * Fetch sender and receiver info associated with the tracking number.
     * @return array
     */
    public function fetchParcelDetails(string $trackingNumber): array;

    /**
     * Sync local parcel status updates back to the courier.
     * @return bool
     */
    public function updateParcelStatus(string $trackingNumber, string $status, array $metadata = []): bool;
}
