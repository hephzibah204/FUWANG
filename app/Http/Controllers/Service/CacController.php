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
        return app(VerificationController::class)->verifyCac($request);
    }
}
