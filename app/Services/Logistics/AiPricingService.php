<?php

namespace App\Services\Logistics;

use App\Models\LogisticsAiPricingModel;
use Illuminate\Support\Facades\Cache;

class AiPricingService
{
    public function adjust(array $features): float
    {
        $base = (float) ($features['base_price'] ?? 0);

        $model = $this->loadModel();
        if (! $model) {
            return $base;
        }

        $x = $this->vectorize($features, $model['feature_keys']);
        $weights = $model['weights'];

        $score = (float) ($weights['bias'] ?? 0);
        foreach ($x as $k => $v) {
            $w = (float) ($weights[$k] ?? 0);
            $score += $w * (float) $v;
        }

        $mult = (float) ($model['multiplier'] ?? 0);
        $pred = $base + ($mult * $score);

        return max(0, $pred);
    }

    public function loadModel(): ?array
    {
        $key = 'logistics.ai_pricing.model.v1';
        $cached = Cache::get($key);
        if (is_array($cached)) {
            return $cached;
        }

        $active = LogisticsAiPricingModel::query()
            ->where('is_active', true)
            ->orderByDesc('trained_at')
            ->first();
        if ($active) {
            $model = [
                'feature_keys' => (array) ($active->feature_keys ?? []),
                'weights' => (array) ($active->weights ?? []),
                'multiplier' => (float) ($active->multiplier ?? 0),
            ];
            Cache::put($key, $model, now()->addMinutes(30));
            return $model;
        }

        $default = [
            'feature_keys' => [
                'distance_km',
                'weight',
                'is_express',
                'is_overnight',
                'is_same_day',
                'is_home_pickup',
                'is_home_delivery',
                'seasonal_factor',
                'fuel_factor',
                'traffic_factor',
                'weather_factor',
            ],
            'weights' => [
                'bias' => 0,
                'distance_km' => 0,
                'weight' => 0,
                'is_express' => 0,
                'is_overnight' => 0,
                'is_same_day' => 0,
                'is_home_pickup' => 0,
                'is_home_delivery' => 0,
                'seasonal_factor' => 0,
                'fuel_factor' => 0,
                'traffic_factor' => 0,
                'weather_factor' => 0,
            ],
            'multiplier' => 0,
        ];

        Cache::put($key, $default, now()->addHours(6));

        return $default;
    }

    private function vectorize(array $features, array $keys): array
    {
        $deliveryType = (string) ($features['delivery_type'] ?? 'standard');
        $pickupMethod = (string) ($features['pickup_method'] ?? 'center_dropoff');
        $deliveryMethod = (string) ($features['delivery_method'] ?? 'home_delivery');

        $map = [
            'distance_km' => (float) ($features['distance_km'] ?? 0),
            'weight' => (float) ($features['weight'] ?? 0),
            'is_express' => $deliveryType === 'express' ? 1 : 0,
            'is_overnight' => $deliveryType === 'overnight' ? 1 : 0,
            'is_same_day' => $deliveryType === 'same_day' ? 1 : 0,
            'is_home_pickup' => $pickupMethod === 'home_pickup' ? 1 : 0,
            'is_home_delivery' => $deliveryMethod === 'home_delivery' ? 1 : 0,
            'seasonal_factor' => (float) ($features['seasonal_factor'] ?? 1.0),
            'fuel_factor' => (float) ($features['fuel_factor'] ?? 1.0),
            'traffic_factor' => (float) ($features['traffic_factor'] ?? 1.0),
            'weather_factor' => (float) ($features['weather_factor'] ?? 1.0),
        ];

        $out = [];
        foreach ($keys as $k) {
            $out[$k] = $map[$k] ?? 0;
        }

        return $out;
    }
}
