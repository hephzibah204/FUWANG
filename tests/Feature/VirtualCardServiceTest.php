<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AccountBalance;
use App\Models\VirtualCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VirtualCardServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_virtual_card_creation_via_flutterwave()
    {
        $this->withoutMiddleware(\App\Http\Middleware\CheckInstallation::class);
        $user = User::factory()->create();
        AccountBalance::create(['email' => $user->email, 'user_balance' => 50000, 'api_key' => 'test']);
        
        config(['services.flutterwave.secret' => 'FLWSECK_TEST-123']);

        Http::fake([
            'api.flutterwave.com/v3/virtual-cards' => Http::response([
                'status' => 'success',
                'message' => 'Virtual card created successfully',
                'data' => [
                    'id' => 'card_12345',
                    'card_pan' => '4123456789012345',
                    'cvv' => '123',
                    'expiration' => '12/26',
                    'amount' => 10,
                    'currency' => 'USD',
                    'is_active' => true
                ]
            ], 200)
        ]);

        $response = $this->actingAs($user)->postJson('/services/virtual-card/create', [
            'card_type' => 'usd',
            'initial_load' => 10
        ]);

        $response->dump();

        $response->assertStatus(200)
                 ->assertJsonPath('status', true)
                 ->assertJsonPath('card.number', '4123456789012345');

        $this->assertDatabaseHas('virtual_cards', [
            'user_id' => $user->id,
            'provider_card_id' => 'card_12345',
            'currency' => 'USD',
        ]);
    }
}
