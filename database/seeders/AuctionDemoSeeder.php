<?php

namespace Database\Seeders;

use App\Models\AuctionBid;
use App\Models\AuctionLot;
use App\Models\AuctionLotImage;
use App\Models\AuctionSeller;
use App\Models\User;
use Illuminate\Database\Seeder;

class AuctionDemoSeeder extends Seeder
{
    public function run(): void
    {
        $sellers = [];
        for ($i = 1; $i <= 5; $i++) {
            $sellers[] = AuctionSeller::create([
                'name' => "Seller {$i}",
                'location' => "Lagos, Nigeria",
                'rating' => 4.5,
                'reviews_count' => 10,
                'verified' => true,
                'avatar_url' => "https://example.com/avatar{$i}.jpg",
                'about' => "Seller {$i} description",
            ]);
        }

        $statuses = ['scheduled', 'live', 'ended', 'cancelled'];
        
        for ($i = 1; $i <= 20; $i++) {
            $seller = $sellers[($i - 1) % 5];
            $status = $statuses[($i - 1) % 4];

            $lot = AuctionLot::create([
                'seller_id' => $seller->id,
                'lot_code' => "LOT-{$i}",
                'title' => "Auction Item {$i}",
                'category' => 'Electronics',
                'location' => 'Lagos',
                'description' => "Description for item {$i}",
                'starting_price' => 1000.00,
                'current_price' => 1500.00,
                'bid_increment' => 100.00,
                'start_at' => now()->subDays(1),
                'end_at' => now()->addDays(2),
                'status' => $status,
                'featured' => $i % 2 === 0,
            ]);

            AuctionLotImage::create([
                'auction_lot_id' => $lot->id,
                'url' => "https://example.com/img{$i}_1.jpg",
                'sort_order' => 1,
            ]);
            AuctionLotImage::create([
                'auction_lot_id' => $lot->id,
                'url' => "https://example.com/img{$i}_2.jpg",
                'sort_order' => 2,
            ]);
        }

        $user = User::first() ?? User::factory()->create();

        $liveLot = AuctionLot::where('status', 'live')->first();
        if ($liveLot) {
            AuctionBid::create([
                'user_id' => $user->id,
                'lot_id' => $liveLot->id,
                'item_name' => $liveLot->title,
                'bid_amount' => 1600.00,
                'status' => 'active',
                'reference' => 'BID-REF-1',
            ]);
        }
    }
}
