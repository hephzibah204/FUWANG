<?php

namespace Database\Seeders;

use App\Models\LogisticsCenter;
use App\Support\NigeriaLocations;
use Illuminate\Database\Seeder;

class LogisticsCentersSeeder extends Seeder
{
    public function run(): void
    {
        $states = NigeriaLocations::stateNames();
        foreach ($states as $state) {
            $city = NigeriaLocations::stateToCityMap()[$state][0] ?? null;

            LogisticsCenter::query()->firstOrCreate([
                'state' => $state,
                'type' => 'pickup',
                'name' => 'FuwaPost Pickup Center - ' . $state,
            ], [
                'city' => $city,
                'availability_status' => 'available',
                'is_active' => true,
            ]);

            LogisticsCenter::query()->firstOrCreate([
                'state' => $state,
                'type' => 'dropoff',
                'name' => 'FuwaPost Drop-off Center - ' . $state,
            ], [
                'city' => $city,
                'availability_status' => 'available',
                'is_active' => true,
            ]);
        }
    }
}

