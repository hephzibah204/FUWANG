<?php

namespace Tests\Feature\Logistics;

use App\Models\LogisticsCenter;
use App\Support\NigeriaLocations;
use Database\Seeders\LogisticsCentersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogisticsCentersCoverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_centers_cover_all_states_and_fct(): void
    {
        $this->seed(LogisticsCentersSeeder::class);

        $states = NigeriaLocations::stateNames();
        $this->assertGreaterThanOrEqual(37, count($states));

        foreach ($states as $state) {
            $this->assertTrue(LogisticsCenter::query()->where('state', $state)->where('type', 'pickup')->exists(), $state . ' pickup missing');
            $this->assertTrue(LogisticsCenter::query()->where('state', $state)->where('type', 'dropoff')->exists(), $state . ' dropoff missing');
        }
    }

    public function test_centers_api_returns_centers_for_state(): void
    {
        $this->seed(LogisticsCentersSeeder::class);

        $state = NigeriaLocations::stateNames()[0] ?? null;
        $this->assertNotNull($state);

        $resp = $this->getJson('/logistics/centers?state=' . urlencode((string) $state) . '&type=pickup');
        $resp->assertOk();
        $resp->assertJsonPath('status', true);
        $this->assertIsArray($resp->json('centers'));
        $this->assertNotEmpty($resp->json('centers'));
    }
}

