<?php

namespace Tests\Feature;

use App\Models\AuctionLot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicAuctionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_auctions_index_renders()
    {
        $response = $this->get(route('public.auctions.index'));

        $response->assertStatus(200);
        $response->assertSee('Live Auctions');
    }

    public function test_public_auctions_index_shows_lots()
    {
        AuctionLot::create([
            'lot_code' => 'LOT-TEST-001',
            'title' => 'Test Auction Item',
            'category' => 'Electronics',
            'location' => 'Lagos',
            'description' => 'Test description',
            'starting_price' => 1000,
            'current_price' => 2500,
            'bid_increment' => 100,
            'start_at' => now()->subHour(),
            'end_at' => now()->addDay(),
            'status' => 'live',
            'featured' => false,
        ]);

        $response = $this->get(route('public.auctions.index'));

        $response->assertStatus(200);
        $response->assertSee('Test Auction Item');
        $response->assertSee('LOT-TEST-001');
    }
}

