<?php

namespace Tests\Feature;

use App\Models\AuctionLot;
use App\Models\AuctionSeller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuctionRealtimeSseTest extends TestCase
{
    use RefreshDatabase;

    public function test_sse_requires_auth(): void
    {
        $this->get(route('realtime.auctions.stream'))
            ->assertRedirect('/login');
    }

    public function test_sse_returns_event_stream_when_authenticated(): void
    {
        $user = User::create([
            'fullname' => 'U',
            'email' => 'u@example.com',
            'password' => Hash::make('Password@123'),
        ]);

        $seller = AuctionSeller::create(['name' => 'Seller']);
        AuctionLot::create([
            'seller_id' => $seller->id,
            'lot_code' => 'A-2000',
            'title' => 'Lot',
            'category' => 'Vehicles',
            'status' => 'live',
            'current_price' => 1000,
            'starting_price' => 1000,
            'bid_increment' => 100,
            'start_at' => now()->subMinute(),
            'end_at' => now()->addMinute(),
        ]);

        $res = $this->actingAs($user)->get(route('realtime.auctions.stream', ['ttl' => 1, 'interval' => 1]));

        $res->assertOk();
        $this->assertStringContainsString('text/event-stream', (string) $res->headers->get('content-type'));
    }
}

