<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomApi;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CacController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $providers = CustomApi::where('service_type', 'cac')->where('status', true)->get();
        $prices = [
            'standard' => SystemSetting::get('cac_price', 1000),
        ];
        
        $myResults = \App\Models\VerificationResult::where('user_id', $user->id)
            ->where('service_type', 'cac')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('services.identity.cac', compact('providers', 'prices', 'myResults'));
    }

    public function verify(Request $request)
    {
        $request->validate([
            'rc_number' => 'required|string',
            'company_name' => 'nullable|string',
            'company_type' => 'required|in:BN,RC,IT',
        ]);

        $user = Auth::user();
        $price = SystemSetting::get('cac_price', 1000);

        if ($user->wallet_balance < $price) {
            return response()->json(['status' => false, 'message' => 'Insufficient wallet balance.']);
        }

        // Logic for calling CAC verification API...
        
        return response()->json(['status' => false, 'message' => 'CAC verification currently in sandbox mode.']);
    }
}
