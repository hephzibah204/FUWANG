<?php

namespace Tests\Feature\Logistics;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LogisticsPricingQuoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_pricing_quote_uses_google_maps_when_configured(): void
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
                                'distance' => ['value' => 120000],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $resp = $this->postJson('/logistics/pricing/quote', [
            'sender_state' => 'Lagos',
            'recipient_state' => 'Oyo',
            'pickup_method' => 'home_pickup',
            'delivery_method' => 'home_delivery',
            'sender_address' => 'Ikeja',
            'recipient_address' => 'Ibadan',
            'weight' => 2.2,
            'delivery_type' => 'standard',
        ]);

        $resp->assertOk();
        $resp->assertJsonPath('status', true);
        $resp->assertJsonPath('distance_km', 120);
    }
}
