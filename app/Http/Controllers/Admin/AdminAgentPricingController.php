<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentAgent;
use App\Models\SystemSetting;
use App\Models\VerificationPrice;
use App\Services\Pricing\PriceResolverService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminAgentPricingController extends Controller
{
    /**
     * Display the Agency Wholesale NIN Pricing Management Console.
     */
    public function index()
    {
        $vp = VerificationPrice::first();
        if (!$vp) {
            $vp = VerificationPrice::create([
                'nin_by_nin_price' => 200.0,
                'nin_by_number_price' => 250.0,
                'nin_by_demography_price' => 300.0,
                'bvn_by_bvn' => 200.0,
                'bvn_by_number' => 250.0,
                'verify_by_tracking_id' => 200.0,
                'validation_price' => 700.0,
                'ipe_clearance_price' => 700.0,
                'personalization_price' => 200.0,
            ]);
        }

        $discountPercentage = (float) SystemSetting::get('agent_discount_percentage', PriceResolverService::DEFAULT_AGENT_DISCOUNT_PERCENT);
        $factor = max(0.0, 1.0 - ($discountPercentage / 100.0));

        $servicesConfig = [
            [
                'key' => 'nin_by_nin_price',
                'agent_key' => 'agent_nin_by_nin_price',
                'name' => 'Standard NIN Verification',
                'code' => 'NIN-STD',
                'category' => 'Identity Verification',
                'icon' => 'fa-regular fa-id-card',
                'description' => 'Real-time 11-digit NIN verification and slip data lookup.',
                'standard' => (float) ($vp->nin_by_nin_price ?? 200.0),
                'override' => $vp->agent_nin_by_nin_price !== null ? (float) $vp->agent_nin_by_nin_price : null,
            ],
            [
                'key' => 'nin_by_number_price',
                'agent_key' => 'agent_nin_by_number_price',
                'name' => 'NIN by Phone Lookup',
                'code' => 'NIN-PHONE',
                'category' => 'Identity Verification',
                'icon' => 'fa-solid fa-phone',
                'description' => 'Locate linked NIN record using MSISDN / phone number.',
                'standard' => (float) ($vp->nin_by_number_price ?? 250.0),
                'override' => $vp->agent_nin_by_number_price !== null ? (float) $vp->agent_nin_by_number_price : null,
            ],
            [
                'key' => 'nin_by_demography_price',
                'agent_key' => 'agent_nin_by_demography_price',
                'name' => 'Demographic Search',
                'code' => 'NIN-DEMO',
                'category' => 'Identity Verification',
                'icon' => 'fa-solid fa-users',
                'description' => 'Biographic lookups using Name, Date of Birth, Gender & State.',
                'standard' => (float) ($vp->nin_by_demography_price ?? 300.0),
                'override' => $vp->agent_nin_by_demography_price !== null ? (float) $vp->agent_nin_by_demography_price : null,
            ],
            [
                'key' => 'verify_by_tracking_id',
                'agent_key' => 'agent_verify_by_tracking_id',
                'name' => 'Verification by Tracking ID',
                'code' => 'NIN-TRACK',
                'category' => 'Enrollment Retrieval',
                'icon' => 'fa-solid fa-fingerprint',
                'description' => 'Look up applicant status and record using NIMC Tracking ID.',
                'standard' => (float) ($vp->verify_by_tracking_id ?? 200.0),
                'override' => $vp->agent_verify_by_tracking_id !== null ? (float) $vp->agent_verify_by_tracking_id : null,
            ],
            [
                'key' => 'validation_price',
                'agent_key' => 'agent_validation_price',
                'name' => 'NIN Validation (NIMC Sync)',
                'code' => 'NIN-VALID',
                'category' => 'Validation & Sync',
                'icon' => 'fa-solid fa-file-shield',
                'description' => 'Resolve "Record Not Found" issues across telcos, banks & immigration.',
                'standard' => (float) ($vp->validation_price ?? 700.0),
                'override' => $vp->agent_validation_price !== null ? (float) $vp->agent_validation_price : null,
            ],
            [
                'key' => 'ipe_clearance_price',
                'agent_key' => 'agent_ipe_clearance_price',
                'name' => 'IPE Clearance',
                'code' => 'NIN-IPE',
                'category' => 'Exception Handling',
                'icon' => 'fa-solid fa-user-check',
                'description' => 'Clear Internal Processing Error (IPE) exceptions without re-enrollment.',
                'standard' => (float) ($vp->ipe_clearance_price ?? 700.0),
                'override' => $vp->agent_ipe_clearance_price !== null ? (float) $vp->agent_ipe_clearance_price : null,
            ],
            [
                'key' => 'personalization_price',
                'agent_key' => 'agent_personalization_price',
                'name' => 'NIN Personalization',
                'code' => 'NIN-PERS',
                'category' => 'Document Generation',
                'icon' => 'fa-solid fa-user-gear',
                'description' => 'Bind tracking ID, generate and activate official digital NIN slip.',
                'standard' => (float) ($vp->personalization_price ?? 200.0),
                'override' => $vp->agent_personalization_price !== null ? (float) $vp->agent_personalization_price : null,
            ],
            [
                'key' => 'nin_modification_price',
                'agent_key' => 'agent_nin_modification_price',
                'name' => 'NIN Modification Request',
                'code' => 'NIN-MOD',
                'category' => 'Data Correction',
                'icon' => 'fa-solid fa-user-pen',
                'description' => 'Name change, DOB correction, address or phone update requests.',
                'standard' => (float) SystemSetting::get('nin_modification_price', 2500.0),
                'override' => SystemSetting::get('agent_nin_modification_price') !== null ? (float) SystemSetting::get('agent_nin_modification_price') : null,
            ],
            [
                'key' => 'nin_face_verification_price',
                'agent_key' => 'agent_nin_face_price',
                'name' => 'Face Biometric Match',
                'code' => 'NIN-FACE',
                'category' => 'Biometrics',
                'icon' => 'fa-solid fa-camera',
                'description' => 'Live selfie photograph matching against NIMC facial database.',
                'standard' => (float) SystemSetting::get('nin_face_verification_price', 250.0),
                'override' => SystemSetting::get('agent_nin_face_verification_price') !== null ? (float) SystemSetting::get('agent_nin_face_verification_price') : null,
            ],
            [
                'key' => 'bvn_by_bvn',
                'agent_key' => 'agent_bvn_by_bvn',
                'name' => 'BVN by BVN Verification',
                'code' => 'BVN-STD',
                'category' => 'Banking Identity',
                'icon' => 'fa-solid fa-building-columns',
                'description' => 'Direct NIBSS verification and account details lookup.',
                'standard' => (float) ($vp->bvn_by_bvn ?? 200.0),
                'override' => $vp->agent_bvn_by_bvn !== null ? (float) $vp->agent_bvn_by_bvn : null,
            ],
            [
                'key' => 'bvn_by_number',
                'agent_key' => 'agent_bvn_by_number',
                'name' => 'BVN by Phone Lookup',
                'code' => 'BVN-PHONE',
                'category' => 'Banking Identity',
                'icon' => 'fa-solid fa-mobile-screen',
                'description' => 'Find linked Bank Verification Number using registered phone number.',
                'standard' => (float) ($vp->bvn_by_number ?? 250.0),
                'override' => $vp->agent_bvn_by_number !== null ? (float) $vp->agent_bvn_by_number : null,
            ],
        ];

        $services = [];
        $overrideCount = 0;

        foreach ($servicesConfig as $item) {
            $discountCalculated = round($item['standard'] * $factor, 2);
            $hasOverride = $item['override'] !== null && $item['override'] > 0;
            if ($hasOverride) {
                $overrideCount++;
                $effective = (float) $item['override'];
            } else {
                $effective = $discountCalculated;
            }

            $savings = max(0.0, $item['standard'] - $effective);
            $effectivePercent = $item['standard'] > 0 ? round(($savings / $item['standard']) * 100, 1) : 0;

            $services[] = array_merge($item, [
                'discount_calculated' => $discountCalculated,
                'effective' => $effective,
                'has_override' => $hasOverride,
                'savings' => $savings,
                'effective_discount_percent' => $effectivePercent,
            ]);
        }

        $agentStats = [
            'total_agents' => EnrollmentAgent::count(),
            'approved_agents' => EnrollmentAgent::where('status', 'approved')->count(),
            'active_overrides' => $overrideCount,
            'default_discount' => $discountPercentage,
        ];

        return view('admin.agents.pricing', compact('services', 'discountPercentage', 'agentStats'));
    }

    /**
     * Update the Agency Wholesale NIN Pricing and Discount Settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'agent_discount_percentage' => 'nullable|numeric|min:0|max:100',
            'agent_nin_by_nin_price' => 'nullable|numeric|min:0',
            'agent_nin_by_number_price' => 'nullable|numeric|min:0',
            'agent_nin_by_demography_price' => 'nullable|numeric|min:0',
            'agent_verify_by_tracking_id' => 'nullable|numeric|min:0',
            'agent_validation_price' => 'nullable|numeric|min:0',
            'agent_ipe_clearance_price' => 'nullable|numeric|min:0',
            'agent_personalization_price' => 'nullable|numeric|min:0',
            'agent_bvn_by_bvn' => 'nullable|numeric|min:0',
            'agent_bvn_by_number' => 'nullable|numeric|min:0',
            'agent_nin_modification_price' => 'nullable|numeric|min:0',
            'agent_nin_face_price' => 'nullable|numeric|min:0',
            'agent_bvn_retrieval_price' => 'nullable|numeric|min:0',
        ]);

        $vp = VerificationPrice::first();
        if (!$vp) {
            $vp = VerificationPrice::create([
                'nin_by_nin_price' => 200.0,
                'nin_by_number_price' => 250.0,
                'nin_by_demography_price' => 300.0,
                'bvn_by_bvn' => 200.0,
                'bvn_by_number' => 250.0,
                'verify_by_tracking_id' => 200.0,
                'validation_price' => 700.0,
                'ipe_clearance_price' => 700.0,
                'personalization_price' => 200.0,
            ]);
        }

        $tableCols = [
            'agent_nin_by_nin_price',
            'agent_nin_by_number_price',
            'agent_nin_by_demography_price',
            'agent_verify_by_tracking_id',
            'agent_validation_price',
            'agent_ipe_clearance_price',
            'agent_personalization_price',
            'agent_bvn_by_bvn',
            'agent_bvn_by_number',
        ];

        $updateData = [];
        foreach ($tableCols as $col) {
            if (Schema::hasColumn('verification_prices', $col)) {
                $updateData[$col] = $request->filled($col) ? (float) $request->input($col) : null;
            }
        }

        if (!empty($updateData)) {
            DB::table('verification_prices')->where('id', $vp->id)->update($updateData);
        }

        // Global discount percentage
        $discountPct = $request->filled('agent_discount_percentage')
            ? (float) $request->input('agent_discount_percentage')
            : PriceResolverService::DEFAULT_AGENT_DISCOUNT_PERCENT;
        SystemSetting::set('agent_discount_percentage', $discountPct, 'pricing');

        // Dynamic SystemSetting prices
        if ($request->filled('agent_nin_modification_price')) {
            SystemSetting::set('agent_nin_modification_price', (float) $request->agent_nin_modification_price, 'pricing');
        } else {
            SystemSetting::set('agent_nin_modification_price', null, 'pricing');
        }

        if ($request->filled('agent_nin_face_price')) {
            SystemSetting::set('agent_nin_face_verification_price', (float) $request->agent_nin_face_price, 'pricing');
        } else {
            SystemSetting::set('agent_nin_face_verification_price', null, 'pricing');
        }

        if ($request->filled('agent_bvn_retrieval_price')) {
            SystemSetting::set('agent_bvn_retrieval_price', (float) $request->agent_bvn_retrieval_price, 'pricing');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Agency wholesale NIN pricing updated successfully.',
            ]);
        }

        return redirect()->route('admin.agents.pricing')->with('success', 'Agency wholesale NIN pricing updated successfully.');
    }

    /**
     * Clear all explicit overrides and revert all services to the global discount formula.
     */
    public function resetOverrides()
    {
        $tableCols = [
            'agent_nin_by_nin_price',
            'agent_nin_by_number_price',
            'agent_nin_by_demography_price',
            'agent_verify_by_tracking_id',
            'agent_validation_price',
            'agent_ipe_clearance_price',
            'agent_personalization_price',
            'agent_bvn_by_bvn',
            'agent_bvn_by_number',
        ];

        $resetData = [];
        foreach ($tableCols as $col) {
            if (Schema::hasColumn('verification_prices', $col)) {
                $resetData[$col] = null;
            }
        }

        $vp = VerificationPrice::first();
        if ($vp && !empty($resetData)) {
            DB::table('verification_prices')->where('id', $vp->id)->update($resetData);
        }

        SystemSetting::set('agent_nin_modification_price', null, 'pricing');
        SystemSetting::set('agent_nin_face_verification_price', null, 'pricing');

        return redirect()->route('admin.agents.pricing')->with('success', 'All explicit agent price overrides cleared. All services now use the auto-calculated discount.');
    }
}
