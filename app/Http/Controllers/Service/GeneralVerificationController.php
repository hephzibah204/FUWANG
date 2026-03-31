<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomApi;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Auth;

class GeneralVerificationController extends Controller
{
    /**
     * Stamp Duty Verification
     */
    public function stampDuty()
    {
        $prices = ['standard' => SystemSetting::get('stamp_duty_price', 100)];
        return view('services.identity.stamp_duty', compact('prices'));
    }

    /**
     * Plate Number Verification
     */
    public function plateNumber()
    {
        $prices = ['standard' => SystemSetting::get('plate_number_price', 150)];
        return view('services.identity.plate_number', compact('prices'));
    }

    /**
     * Credit Bureau Check
     */
    public function creditBureau()
    {
        $prices = ['standard' => SystemSetting::get('credit_bureau_price', 2500)];
        return view('services.identity.credit_bureau', compact('prices'));
    }

    /**
     * General Validation Hub
     */
    public function validation()
    {
        return view('services.identity.validation');
    }

    /**
     * Clearance Certificates
     */
    public function clearance()
    {
        return view('services.identity.clearance');
    }
}
