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
            'api_provider_id' => 'nullable|integer',
            'verification_type' => 'nullable|string',
        ]);

        if ($request->filled('api_provider_id') && $request->filled('verification_type')) {
            $type = \App\Models\CustomApiVerificationType::where('custom_api_id', $request->api_provider_id)
                ->where('type_key', $request->verification_type)
                ->where('status', true)
                ->first();

            if (!$type) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'verification_type' => ['The selected verification type is inactive or invalid.'],
                ]);
            }
        }

        $user = Auth::user();
        $price = SystemSetting::get('cac_price', 1000);

        if ($user->wallet_balance < $price) {
            return response()->json(['status' => false, 'message' => 'Insufficient wallet balance.']);
        }

        // Logic for calling CAC verification API...
        
        return response()->json(['status' => false, 'message' => 'CAC verification currently in sandbox mode.']);
    }
}
