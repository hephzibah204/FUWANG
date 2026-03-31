<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AuctionLot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuctionWatchlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_to_watchlist()
    {
        $this->withoutMiddleware(\App\Http\Middleware\CheckInstallation::class);
        $user = User::factory()->create();
        $lot = AuctionLot::create([
            'lot_code' => 'LOT-1234',
            'title' => 'Test Item',
            'description' => 'A test item',
            'category' => 'Electronics',
            'starting_price' => 1000,
            'current_price' => 1000,
            'bid_increment' => 100,
            'status' => 'live',
            'start_at' => now()->subDay(),
            'end_at' => now()->addDays(2),
        ]);

        $response = $this->actingAs($user)->postJson('/services/auctions/watchlist/add', [
            'lot_code' => 'LOT-1234'
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('ok', true);

        $this->assertDatabaseHas('auction_watchlists', [
            'user_id' => $user->id,
            'lot_code' => 'LOT-1234'
        ]);
    }
}
