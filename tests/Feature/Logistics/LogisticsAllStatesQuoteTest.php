<?php

namespace Tests\Feature\Logistics;

use App\Support\NigeriaLocations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LogisticsAllStatesQuoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_quote_endpoint_works_across_all_states(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        config()->set('services.google_maps.api_key', 'test-key');
        Http::fake([
            'https://maps.googleapis.com/maps/api/geocode/json*' => Http::response([
                'status' => 'OK',
                'results' => [
                    [
                        'place_id' => 'p1',
                        'formatted_address' => 'Test',
                        'geometry' => ['location' => ['lat' => 6.5, 'lng' => 3.3]],
                    ],
                ],
            ]),
            'https://maps.googleapis.com/maps/api/distancematrix/json*' => Http::response([
                'status' => 'OK',
                'rows' => [
                    [
                        'elements' => [
                            [
                                'status' => 'OK',
                                'distance' => ['value' => 100000],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $states = NigeriaLocations::stateNames();
        foreach ($states as $state) {
            $resp = $this->postJson('/logistics/pricing/quote', [
                'sender_state' => $state,
                'recipient_state' => $state,
                'pickup_method' => 'home_pickup',
                'delivery_method' => 'home_delivery',
                'sender_address' => 'Test address',
                'recipient_address' => 'Test address',
                'weight' => 1.2,
                'delivery_type' => 'standard',
            ]);
            $resp->assertOk();
            $resp->assertJsonPath('status', true);
        }
    }
}

