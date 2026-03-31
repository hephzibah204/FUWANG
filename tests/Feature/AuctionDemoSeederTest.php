<?php

namespace Tests\Feature;

use App\Models\AuctionBid;
use App\Models\AuctionLot;
use App\Models\AuctionLotImage;
use App\Models\AuctionSeller;
use Database\Seeders\AuctionDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AuctionDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_auction_demo_seeder_creates_expected_records()
    {
        Artisan::call('db:seed', ['--class' => AuctionDemoSeeder::class]);

        $this->assertSame(5, AuctionSeller::count());
        $this->assertSame(20, AuctionLot::count());
        $this->assertGreaterThanOrEqual(40, AuctionLotImage::count());
        $this->assertGreaterThan(0, AuctionBid::count());

        $this->assertGreaterThan(0, AuctionLot::where('status', 'scheduled')->count());
        $this->assertGreaterThan(0, AuctionLot::where('status', 'live')->count());
        $this->assertGreaterThan(0, AuctionLot::where('status', 'ended')->count());
        $this->assertGreaterThan(0, AuctionLot::where('status', 'cancelled')->count());
    }
}

