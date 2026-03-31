<?php

namespace App\Http\Controllers;

use App\Models\CustomApi;
use App\Models\VerificationPrice;
use Illuminate\Http\Request;

class PriceListController extends Controller
{
    /**
     * Display the dynamic price list.
     */
    public function index()
    {
        // Get all active APIs and group by service type
        $customPrices = CustomApi::where('status', true)
            ->with(['verificationTypes' => function ($q) {
                $q->where('status', true)->orderBy('sort_order')->orderBy('label');
            }])
            ->orderBy('service_type')
            ->orderBy('name')
            ->get()
            ->groupBy('service_type');

        // Define high-level categories for better grouping
        $categories = [
            'Identity Verification' => [
                'nin', 'nin_verification', 'bvn', 'bvn_verification', 'address_verification', 
                'drivers_license', 'biometric_verification', 'passport_verification', 
                'voters_card_verification', 'cac_verification', 'tin_verification', 
                'nin_face_verification', 'bvn_matching', 'bvn_nin_phone_verification'
            ],
            'VTU & Utility' => [
                'vtu_airtime', 'vtu_data', 'education_waec', 'education_waec_registration', 
                'insurance_motor', 'stamp_duty', 'plate_number_verification'
            ],
            'Payments & Finance' => [
                'payment', 'credit_bureau_advance'
            ]
        ];

        // Fallback or legacy prices from VerificationPrice table
        $legacyPrices = VerificationPrice::first();

        return view('services.price-list', compact('customPrices', 'legacyPrices', 'categories'));
    }
}
