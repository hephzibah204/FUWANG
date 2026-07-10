<?php

namespace App\Services\Logistics;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GoogleMapsService
{
    public function isConfigured(): bool
    {
        return (string) config('services.google_maps.api_key', '') !== '';
    }

    public function geocode(string $address): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $key = 'gmaps.geocode.' . hash('sha256', $address);

        return Cache::remember($key, now()->addDays(30), function () use ($address) {
            $res = Http::timeout(10)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => $address,
                'key' => config('services.google_maps.api_key'),
            ]);

            if (! $res->ok()) {
                return null;
            }

            $data = $res->json();
            if (! is_array($data) || ($data['status'] ?? null) !== 'OK') {
                return null;
            }

            $first = $data['results'][0] ?? null;
            $loc = $first['geometry']['location'] ?? null;
            if (! is_array($loc) || ! isset($loc['lat'], $loc['lng'])) {
                return null;
            }

            return [
                'lat' => (float) $loc['lat'],
                'lng' => (float) $loc['lng'],
                'place_id' => $first['place_id'] ?? null,
                'formatted_address' => $first['formatted_address'] ?? null,
            ];
        });
    }

    public function distanceKm(array $origin, array $destination): ?float
    {
        if (! $this->isConfigured()) {
            return null;
        }
        if (! isset($origin['lat'], $origin['lng'], $destination['lat'], $destination['lng'])) {
            return null;
        }

        $cacheKey = 'gmaps.distance.' . hash('sha256', json_encode([$origin, $destination]));

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($origin, $destination) {
            $res = Http::timeout(10)->get('https://maps.googleapis.com/maps/api/distancematrix/json', [
                'origins' => $origin['lat'] . ',' . $origin['lng'],
                'destinations' => $destination['lat'] . ',' . $destination['lng'],
                'key' => config('services.google_maps.api_key'),
                'departure_time' => 'now',
                'traffic_model' => 'best_guess',
            ]);

            if (! $res->ok()) {
                return null;
            }

            $data = $res->json();
            if (! is_array($data) || ($data['status'] ?? null) !== 'OK') {
                return null;
            }

            $elem = $data['rows'][0]['elements'][0] ?? null;
            if (! is_array($elem) || ($elem['status'] ?? null) !== 'OK') {
                return null;
            }

            $meters = $elem['distance']['value'] ?? null;
            if (! is_numeric($meters)) {
                return null;
            }

            return round(((float) $meters) / 1000, 2);
        });
    }
}

