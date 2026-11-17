<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Service\VtuPurchaseController;
use Illuminate\Http\Request;

class VtuApiController extends Controller
{
    private function normalize(Request $request): void
    {
        $request->headers->set('Accept', 'application/json');
        $aliases = ['provider' => 'serviceID', 'smartcard_number' => 'smart_card_number', 'meter_type' => 'variation_code'];
        foreach ($aliases as $old => $new) {
            if (!$request->filled($new) && $request->filled($old)) { $request->merge([$new => $request->input($old)]); }
        }
        if ($request->filled('plan_id') && !$request->filled('variation_code')) { $request->merge(['variation_code' => $request->plan_id]); }
    }
    public function airtime(Request $request) { $this->normalize($request); return app(VtuPurchaseController::class)->buyAirtime($request); }
    public function data(Request $request) { $this->normalize($request); return app(VtuPurchaseController::class)->buyData($request); }
    public function cable(Request $request) { $this->normalize($request); return app(VtuPurchaseController::class)->buyCable($request); }
    public function electricity(Request $request) { $this->normalize($request); return app(VtuPurchaseController::class)->buyElectricity($request); }
    public function validateElectricity(Request $request) { $this->normalize($request); return app(VtuPurchaseController::class)->validateElectricity($request); }
    public function validateCable(Request $request) { $this->normalize($request); return app(VtuPurchaseController::class)->validateCable($request); }
}
