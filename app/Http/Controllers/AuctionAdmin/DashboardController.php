<?php

namespace App\Http\Controllers\AuctionAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuctionBid;
use App\Models\AuctionLot;
use App\Models\AuctionSeller;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'lots_total' => AuctionLot::query()->count(),
            'lots_live' => AuctionLot::query()->where('status', 'live')->count(),
            'bids_total' => AuctionBid::query()->count(),
            'sellers_total' => AuctionSeller::query()->count(),
        ];

        $recentLots = AuctionLot::query()->latest()->limit(10)->get();

        return view('auction_admin.dashboard', compact('stats', 'recentLots'));
    }
}

