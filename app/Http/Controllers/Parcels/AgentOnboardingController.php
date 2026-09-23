<?php

namespace App\Http\Controllers\Parcels;

use App\Http\Controllers\Controller;
use App\Models\ParcelAgent;
use App\Models\ParcelShop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentOnboardingController extends Controller
{
    public function showRegistrationForm()
    {
        // Check if user is already an agent
        $user = Auth::user();
        if ($user->parcelAgent) {
            return redirect()->route('parcels.dashboard');
        }

        return view('parcels.onboarding.register');
    }

    public function submitRegistration(Request $request)
    {
        $request->validate([
            'shop_name' => 'required|string|max:255',
            'shop_address' => 'required|string|max:500',
            'state' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'nin_number' => 'required|string|min:11|max:11',
        ]);

        $user = Auth::user();
        
        if ($user->parcelAgent) {
            return redirect()->route('parcels.dashboard');
        }

        // Create Shop and Agent in a transaction
        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $user) {
            $shop = ParcelShop::create([
                'name' => $request->input('shop_name'),
                'address' => $request->input('shop_address'),
                'state' => $request->input('state'),
                'city' => $request->input('city'),
                'is_active' => false,
            ]);

            ParcelAgent::create([
                'user_id' => $user->id,
                'shop_id' => $shop->id,
                'nin_number' => $request->input('nin_number'),
                'status' => 'pending',
            ]);
        });

        return redirect()->route('parcels.dashboard')->with('success', 'Your application to become a Parcel Agent has been submitted. Please wait for admin approval.');
    }
}
