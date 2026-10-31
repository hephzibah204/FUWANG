<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomApi;
use App\Models\ApiCenter;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DriversLicenseController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $providers = CustomApi::where('service_type', 'drivers_license')->where('status', true)->get();
        $prices = [
            'standard' => SystemSetting::get('drivers_license_price', 250),
        ];
        
        // Fetch history if needed
        $myResults = \App\Models\VerificationResult::where('user_id', $user->id)
            ->where('service_type', 'drivers_license')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('services.identity.drivers_license', compact('providers', 'prices', 'myResults'));
    }

    public function verify(Request $request)
    {
        return app(VerificationController::class)->verifyDriversLicense($request);
    }
}
