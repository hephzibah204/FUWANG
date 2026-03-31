<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomApi;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BiometricController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $providers = CustomApi::where('service_type', 'biometric')->where('status', true)->get();
        $prices = [
            'standard' => SystemSetting::get('biometric_price', 500),
        ];
        
        $myResults = \App\Models\VerificationResult::where('user_id', $user->id)
            ->where('service_type', 'biometric')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('services.identity.biometric', compact('providers', 'prices', 'myResults'));
    }

    public function verify(Request $request)
    {
        $request->validate([
            'image' => 'required|string', // base64 image string
            'identifier' => 'required|string', // NIN or BVN
            'type' => 'required|in:nin,bvn',
        ]);

        $user = Auth::user();
        $price = SystemSetting::get('biometric_price', 500);

        if ($user->wallet_balance < $price) {
            return response()->json(['status' => false, 'message' => 'Insufficient wallet balance.']);
        }

        // Logic for calling biometric matching API...
        
        return response()->json(['status' => false, 'message' => 'Biometric matching currently in sandbox mode.']);
    }
}
