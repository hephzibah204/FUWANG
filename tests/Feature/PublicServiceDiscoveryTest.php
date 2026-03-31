<?php

namespace Tests\Feature;

use App\Models\AuctionLot;
use App\Models\AuctionSeller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicServiceDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_explore_index_is_public(): void
    {
        $this->get('/explore')
            ->assertOk()
            ->assertSee('Explore Services', false);
    }

    public function test_explore_service_page_is_public(): void
    {
        $this->get('/explore/identity-verification')
            ->assertOk()
            ->assertSee('Identity Verification', false)
            ->assertSee('Sign in to use', false);
    }

    public function test_public_auctions_pages_render(): void
    {
        $seller = AuctionSeller::create([
            'name' => 'Seller',
            'verified' => true,
        ]);

        $lot = AuctionLot::create([
            'seller_id' => $seller->id,
            'lot_code' => 'A-1000',
            'title' => 'Test Lot',
            'category' => 'Vehicles',
            'location' => 'Abuja',
            'current_price' => 1000,
            'starting_price' => 1000,
            'bid_increment' => 100,
            'status' => 'live',
            'start_at' => now()->subHour(),
            'end_at' => now()->addHour(),
        ]);

        $this->get('/explore/auctions')
            ->assertOk()
            ->assertSee('Live Auctions', false)
            ->assertSee('Test Lot', false);

        $this->get('/explore/auctions/' . $lot->lot_code)
            ->assertOk()
            ->assertSee('Test Lot', false)
            ->assertSee('Sign in to bid or watch', false);
    }
}

