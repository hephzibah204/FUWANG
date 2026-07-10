<?php

namespace App\Services\Logistics;

use App\Models\LogisticsCenter;
use App\Models\SystemSetting;

class LogisticsPricingEngine
{
    public function quote(array $input, array $options = []): array
    {
        $weight = (float) ($input['weight'] ?? 0);
        $deliveryType = (string) ($input['delivery_type'] ?? 'standard');
        $pickupMethod = (string) ($input['pickup_method'] ?? 'center_dropoff');
        $deliveryMethod = (string) ($input['delivery_method'] ?? 'home_delivery');
        $requireGeocode = (bool) ($options['require_geocode'] ?? false);

        $base = (float) SystemSetting::get('logistics_base_cost', 1500);
        $weightMultiplier = (float) SystemSetting::get('logistics_weight_multiplier', 500);
        $perKmRate = (float) SystemSetting::get('logistics_per_km_rate', 50);
        $homePickupFee = (float) SystemSetting::get('logistics_home_pickup_fee', 1000);
        $homeDeliveryFee = (float) SystemSetting::get('logistics_home_delivery_fee', 1000);

        $speedMultipliers = [
            'standard' => (float) SystemSetting::get('logistics_std_mult', 1),
            'express' => (float) SystemSetting::get('logistics_exp_mult', 1.5),
            'overnight' => (float) SystemSetting::get('logistics_ovn_mult', 2),
            'same_day' => (float) SystemSetting::get('logistics_sameday_mult', 2.5),
        ];

        $origin = $this->resolveOrigin($input);
        $destination = $this->resolveDestination($input);

        $maps = app(GoogleMapsService::class);
        if ($requireGeocode && ! $maps->isConfigured()) {
            return [
                'ok' => false,
                'message' => 'Geolocation service is not configured. Please try again later.',
            ];
        }

        if ($requireGeocode && $maps->isConfigured()) {
            if ($pickupMethod === 'home_pickup' && $origin === null) {
                return [
                    'ok' => false,
                    'message' => 'Unable to verify pickup address. Please enter a more precise address.',
                ];
            }
            if ($deliveryMethod === 'home_delivery' && $destination === null) {
                return [
                    'ok' => false,
                    'message' => 'Unable to verify delivery address. Please enter a more precise address.',
                ];
            }
            if ($pickupMethod === 'center_dropoff' && !empty($input['pickup_center_id']) && $origin === null) {
                return [
                    'ok' => false,
                    'message' => 'Pickup center is missing location coordinates. Please contact support or select another center.',
                ];
            }
            if ($deliveryMethod === 'center_pickup' && !empty($input['dropoff_center_id']) && $destination === null) {
                return [
                    'ok' => false,
                    'message' => 'Drop-off center is missing location coordinates. Please contact support or select another center.',
                ];
            }
        }

        $distanceKm = $this->resolveDistanceKm($origin, $destination, $input);
        if ($requireGeocode && $maps->isConfigured() && $distanceKm === null) {
            return [
                'ok' => false,
                'message' => 'Unable to calculate distance for this route at the moment. Please try again.',
            ];
        }

        $weightSurcharge = ceil(max($weight, 0)) * $weightMultiplier;
        $distanceSurcharge = $distanceKm !== null ? ($distanceKm * $perKmRate) : 0.0;
        $pickupSurcharge = $pickupMethod === 'home_pickup' ? $homePickupFee : 0.0;
        $deliverySurcharge = $deliveryMethod === 'home_delivery' ? $homeDeliveryFee : 0.0;

        $subtotal = $base + $weightSurcharge + $distanceSurcharge + $pickupSurcharge + $deliverySurcharge;
        $mult = $speedMultipliers[$deliveryType] ?? $speedMultipliers['standard'];
        $raw = $subtotal * $mult;

        $ai = app(AiPricingService::class)->adjust([
            'base_price' => $raw,
            'delivery_type' => $deliveryType,
            'pickup_method' => $pickupMethod,
            'delivery_method' => $deliveryMethod,
            'weight' => $weight,
            'distance_km' => $distanceKm,
            'dimensions' => [
                'length_cm' => $input['package_length_cm'] ?? null,
                'width_cm' => $input['package_width_cm'] ?? null,
                'height_cm' => $input['package_height_cm'] ?? null,
            ],
        ]);

        $total = round(max(0, $ai), 2);

        return [
            'ok' => true,
            'total' => $total,
            'distance_km' => $distanceKm,
            'origin' => $origin,
            'destination' => $destination,
            'breakdown' => [
                'base' => $base,
                'weight_surcharge' => $weightSurcharge,
                'distance_surcharge' => round($distanceSurcharge, 2),
                'home_pickup_fee' => $pickupSurcharge,
                'home_delivery_fee' => $deliverySurcharge,
                'speed_multiplier' => $mult,
                'subtotal' => round($subtotal, 2),
                'pre_ai_total' => round($raw, 2),
                'final_total' => $total,
            ],
        ];
    }

    private function resolveOrigin(array $input): ?array
    {
        $pickupMethod = (string) ($input['pickup_method'] ?? 'center_dropoff');
        if ($pickupMethod === 'center_dropoff' && ! empty($input['pickup_center_id'])) {
            $center = LogisticsCenter::query()->find((int) $input['pickup_center_id']);
            if ($center && $center->lat !== null && $center->lng !== null) {
                return ['lat' => (float) $center->lat, 'lng' => (float) $center->lng];
            }
        }

        $address = (string) ($input['sender_address'] ?? '');
        $state = (string) ($input['sender_state'] ?? '');
        if ($address !== '' && $state !== '') {
            $geo = app(GoogleMapsService::class)->geocode($address . ', ' . $state . ', Nigeria');
            if ($geo) {
                return ['lat' => $geo['lat'], 'lng' => $geo['lng']];
            }
        }

        return null;
    }

    private function resolveDestination(array $input): ?array
    {
        $deliveryMethod = (string) ($input['delivery_method'] ?? 'home_delivery');
        if ($deliveryMethod === 'center_pickup' && ! empty($input['dropoff_center_id'])) {
            $center = LogisticsCenter::query()->find((int) $input['dropoff_center_id']);
            if ($center && $center->lat !== null && $center->lng !== null) {
                return ['lat' => (float) $center->lat, 'lng' => (float) $center->lng];
            }
        }

        $address = (string) ($input['recipient_address'] ?? '');
        $state = (string) ($input['recipient_state'] ?? '');
        if ($address !== '' && $state !== '') {
            $geo = app(GoogleMapsService::class)->geocode($address . ', ' . $state . ', Nigeria');
            if ($geo) {
                return ['lat' => $geo['lat'], 'lng' => $geo['lng']];
            }
        }

        return null;
    }

    private function resolveDistanceKm(?array $origin, ?array $destination, array $input): ?float
    {
        if ($origin && $destination) {
            $km = app(GoogleMapsService::class)->distanceKm($origin, $destination);
            if ($km !== null) {
                return $km;
            }
        }

        $senderState = (string) ($input['sender_state'] ?? '');
        $recipientState = (string) ($input['recipient_state'] ?? '');
        if ($senderState === '' || $recipientState === '') {
            return null;
        }

        if ($senderState === $recipientState) {
            return (float) SystemSetting::get('logistics_default_intra_state_km', 25);
        }

        return (float) SystemSetting::get('logistics_default_inter_state_km', 450);
    }
}
