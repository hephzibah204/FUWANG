<?php

namespace Tests\Feature;

use App\Models\AccountBalance;
use App\Models\AuctionBid;
use App\Models\AuctionLot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAuctionBiddingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_bid_with_insufficient_wallet_balance(): void
    {
        $user = User::factory()->create(['kyc_tier' => 2]);
        AccountBalance::create([
            'user_id' => $user->id,
            'user_balance' => 500.00,
        ]);

        $lot = AuctionLot::create([
            'lot_code' => 'LOT-BID-001',
            'title' => 'Vintage Camera',
            'category' => 'Electronics',
            'location' => 'Lagos',
            'description' => 'Test description',
            'starting_price' => 1000,
            'current_price' => 1000,
            'bid_increment' => 100,
            'start_at' => now()->subHour(),
            'end_at' => now()->addDay(),
            'status' => 'live',
        ]);

        $response = $this->actingAs($user)->postJson(route('services.auctions.bid'), [
            'lot_code' => $lot->lot_code,
            'amount' => 1100,
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'ok' => false,
            'message' => 'Insufficient wallet balance to place this bid.',
        ]);
    }

    public function test_user_can_place_bid_when_funds_sufficient(): void
    {
        $user = User::factory()->create(['kyc_tier' => 2]);
        AccountBalance::create([
            'user_id' => $user->id,
            'user_balance' => 5000.00,
        ]);

        $lot = AuctionLot::create([
            'lot_code' => 'LOT-BID-002',
            'title' => 'Vintage Camera 2',
            'category' => 'Electronics',
            'location' => 'Lagos',
            'description' => 'Test description',
            'starting_price' => 1000,
            'current_price' => 1000,
            'bid_increment' => 100,
            'start_at' => now()->subHour(),
            'end_at' => now()->addDay(),
            'status' => 'live',
        ]);

        $response = $this->actingAs($user)->postJson(route('services.auctions.bid'), [
            'lot_code' => $lot->lot_code,
            'amount' => 1200,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'ok' => true,
            'message' => 'Bid placed successfully!',
        ]);

        $this->assertDatabaseHas('auction_bids', [
            'user_id' => $user->id,
            'lot_id' => $lot->lot_code,
            'bid_amount' => 1200,
            'status' => 'winning',
        ]);
    }

    public function test_user_cannot_overcommit_wallet_balance_across_multiple_live_lots(): void
    {
        $user = User::factory()->create(['kyc_tier' => 2]);
        AccountBalance::create([
            'user_id' => $user->id,
            'user_balance' => 3000.00,
        ]);

        $lotA = AuctionLot::create([
            'lot_code' => 'LOT-BID-A',
            'title' => 'Laptop A',
            'category' => 'Electronics',
            'location' => 'Lagos',
            'description' => 'Test description',
            'starting_price' => 1000,
            'current_price' => 1000,
            'bid_increment' => 100,
            'start_at' => now()->subHour(),
            'end_at' => now()->addDay(),
            'status' => 'live',
        ]);

        $lotB = AuctionLot::create([
            'lot_code' => 'LOT-BID-B',
            'title' => 'Laptop B',
            'category' => 'Electronics',
            'location' => 'Lagos',
            'description' => 'Test description',
            'starting_price' => 1000,
            'current_price' => 1000,
            'bid_increment' => 100,
            'start_at' => now()->subHour(),
            'end_at' => now()->addDay(),
            'status' => 'live',
        ]);

        // Place winning bid of 2,000 on Lot A
        $responseA = $this->actingAs($user)->postJson(route('services.auctions.bid'), [
            'lot_code' => $lotA->lot_code,
            'amount' => 2000,
        ]);
        $responseA->assertStatus(200);

        // Attempt to place bid of 2,000 on Lot B when only 1,000 is remaining uncommitted
        $responseB = $this->actingAs($user)->postJson(route('services.auctions.bid'), [
            'lot_code' => $lotB->lot_code,
            'amount' => 2000,
        ]);
        $responseB->assertStatus(422);
        $responseB->assertJson([
            'ok' => false,
            'message' => 'Insufficient available wallet balance. You have ₦2,000.00 committed to other active winning bids.',
        ]);
    }
}
