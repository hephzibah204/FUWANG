<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomApi;
use App\Models\FeatureToggle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ServiceManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage_services');
    }

    public function index()
    {
        $catalog = [
            [
                'group' => 'Identity & Trust',
                'name' => 'NIN Suite',
                'feature_key' => 'nin_verification',
                'custom_api_service_type' => 'nin',
            ],
            [
                'group' => 'Identity & Trust',
                'name' => 'BVN Suite',
                'feature_key' => 'bvn_verification',
                'custom_api_service_type' => 'bvn_verification',
            ],
            [
                'group' => 'Identity & Trust',
                'name' => 'Verification Hub (DL, Bio, Stamp, CAC, TIN, etc.)',
                'feature_key' => 'identity_verification',
                'custom_api_service_type' => null,
            ],
            [
                'group' => 'Legal',
                'name' => 'Legal Hub',
                'feature_key' => 'legal_services',
                'custom_api_service_type' => null,
            ],
            [
                'group' => 'Utility',
                'name' => 'VTU (Airtime & Data)',
                'feature_key' => 'vtu_services',
                'custom_api_service_type' => null,
            ],
            [
                'group' => 'Education',
                'name' => 'Education Services (WAEC/NECO)',
                'feature_key' => 'education_services',
                'custom_api_service_type' => null,
            ],
            [
                'group' => 'Insurance',
                'name' => 'Insurance (Motor)',
                'feature_key' => 'insurance_services',
                'custom_api_service_type' => null,
            ],
            [
                'group' => 'Payments',
                'name' => 'Agency Banking',
                'feature_key' => 'agency_banking',
                'custom_api_service_type' => null,
            ],
            [
                'group' => 'Payments',
                'name' => 'Logistics',
                'feature_key' => 'logistics',
                'custom_api_service_type' => null,
            ],
            [
                'group' => 'Payments',
                'name' => 'Virtual Cards',
                'feature_key' => 'virtual_cards',
                'custom_api_service_type' => null,
            ],
            [
                'group' => 'Payments',
                'name' => 'FX Exchange',
                'feature_key' => 'fx_exchange',
                'custom_api_service_type' => null,
            ],
            [
                'group' => 'Payments',
                'name' => 'Invoicing',
                'feature_key' => 'invoicing',
                'custom_api_service_type' => null,
            ],
        ];

        $featureToggles = Cache::remember('admin:feature_toggles', now()->addSeconds(30), function () {
            return FeatureToggle::all()->keyBy('feature_name');
        });

        $customApiStats = Cache::remember('admin:custom_api_stats', now()->addSeconds(30), function () {
            return CustomApi::query()
                ->select([
                    'service_type',
                    DB::raw('COUNT(*) as total_count'),
                    DB::raw('SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as active_count'),
                ])
                ->groupBy('service_type')
                ->get()
                ->keyBy('service_type');
        });

        return view('admin.services.index', compact('catalog', 'featureToggles', 'customApiStats'));
    }

    public function setToggle(Request $request, string $feature)
    {
        $request->validate([
            'offline_message' => 'nullable|string|max:255',
        ]);

        $featureKey = strtolower($feature);
        $toggle = FeatureToggle::firstOrNew(['feature_name' => $featureKey]);
        $toggle->is_active = $request->boolean('is_active');
        $toggle->offline_message = $request->offline_message ?? $toggle->offline_message;
        $toggle->save();

        Cache::forget('feature_toggle:' . $featureKey);
        Cache::forget('admin:feature_toggles');

        return back()->with('success', 'Service availability updated.');
    }
}
