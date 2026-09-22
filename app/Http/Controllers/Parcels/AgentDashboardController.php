<?php

namespace App\Http\Controllers\Parcels;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AgentDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Require the user to be a verified agent
        $agent = $request->user()->parcelAgent;

        if (!$agent) {
            return redirect()->route('parcels.register');
        }

        $shop = $agent->shop;

        // If pending, just show empty stats or let the view show a pending badge
        if ($agent->status !== 'approved') {
            $customerToCollect = 0;
            $driverToCollect = 0;
            $totalInShop = 0;
            $defective = 0;
            return view('parcels.dashboard', compact('shop', 'agent', 'customerToCollect', 'driverToCollect', 'totalInShop', 'defective'));
        }

        // Inventory counts
        $customerToCollect = $shop->parcels()->where('status', 'driver_dropped_off')->count();
        $driverToCollect = $shop->parcels()->whereIn('status', ['customer_dropped_off', 'rejected'])->count();
        $totalInShop = $customerToCollect + $driverToCollect;
        $defective = $shop->parcels()->where('condition', 'damaged')->count();

        // Pass counts to a view (to be created)
        return view('parcels.dashboard', compact('shop', 'agent', 'customerToCollect', 'driverToCollect', 'totalInShop', 'defective'));
    }
}
