<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminAuditLog;
use App\Models\SystemSetting;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    public function index()
    {
        $settings      = DB::table('settings')->first();
        $apiSettings   = DB::table('api_settings')->first();
        $apiCenter     = DB::table('api_centers')->first();
        $notification  = DB::table('notifying_centers')->first();
        $verifyPrices  = DB::table('verification_prices')->first();
        $idPrices      = null;
        if (DB::getSchemaBuilder()->hasTable('id_verification_prices')) {
            $idPrices = DB::table('id_verification_prices')->first();
        }
        $manualFunding = DB::table('manual_funding')->first(); // verify if this is singular in db
        $notaryDocs    = DB::table('notary_settings')->get();
        $systemSettings = \App\Models\SystemSetting::all()->groupBy('group');
        $gateways = \App\Models\PaymentGateway::orderBy('priority', 'asc')->get();
        $admin = Auth::guard('admin')->user();
        $canManageSecurity = false;
        if ($admin) {
            $canManageSecurity = (($admin->is_super_admin ?? false) || (($admin->role ?? null) === 'superadmin')) || Admin::count() <= 1;
        }

        $verifymeWebhookIps = (string) SystemSetting::get('verifyme_webhook_ips', '');
        $verifymeWebhookSecretSet = (bool) SystemSetting::get('verifyme_webhook_secret', '');
        $verifymeWebhookSecretUpdatedAt = SystemSetting::query()
            ->where('key', 'verifyme_webhook_secret')
            ->value('updated_at');

        $securityAuditLogs = AdminAuditLog::query()
            ->with(['admin'])
            ->where('action', 'like', 'security.%')
            ->latest()
            ->limit(30)
            ->get();

        return view('admin.settings.index', compact(
            'settings', 'apiSettings', 'apiCenter', 'notification',
            'verifyPrices', 'idPrices', 'manualFunding', 'notaryDocs', 'systemSettings',
            'gateways',
            'canManageSecurity',
            'verifymeWebhookIps',
            'verifymeWebhookSecretSet',
            'verifymeWebhookSecretUpdatedAt',
            'securityAuditLogs'
        ));
    }

    public function updateNotification(Request $request)
    {
        $request->validate([
            'notification' => 'required|string|max:500',
            'low_balance_threshold' => 'nullable|numeric|min:0',
        ]);

        DB::table('notifying_centers')->updateOrInsert(
            ['id' => DB::table('notifying_centers')->min('id') ?? 1],
            ['notification' => $request->notification]
        );

        if ($request->has('low_balance_threshold')) {
            SystemSetting::set('low_balance_threshold', $request->low_balance_threshold, 'notifications');
        }

        return response()->json(['status' => true, 'message' => 'Notification settings updated successfully.']);
    }

    public function updatePricing(Request $request)
    {
        $request->validate([
            'nin_by_nin_price'          => 'required|numeric|min:0',
            'nin_by_number_price'       => 'required|numeric|min:0',
            'nin_by_demography_price'   => 'required|numeric|min:0',
            'bvn_by_bvn'                => 'required|numeric|min:0',
            'bvn_by_number'             => 'required|numeric|min:0',
            'verify_by_tracking_id'     => 'required|numeric|min:0',
            'validation_price'          => 'required|numeric|min:0',
            'ipe_clearance_price'       => 'required|numeric|min:0',
            'personalization_price'     => 'required|numeric|min:0',
        ]);

        DB::table('verification_prices')->where('id', 1)->update([
            'nin_by_nin_price'        => $request->nin_by_nin_price,
            'nin_by_number_price'     => $request->nin_by_number_price,
            'nin_by_demography_price' => $request->nin_by_demography_price,
            'bvn_by_bvn'              => $request->bvn_by_bvn,
            'bvn_by_number'           => $request->bvn_by_number,
            'verify_by_tracking_id'   => $request->verify_by_tracking_id,
            'validation_price'        => $request->validation_price,
            'ipe_clearance_price'     => $request->ipe_clearance_price,
            'personalization_price'   => $request->personalization_price,
        ]);

        return response()->json(['status' => true, 'message' => 'Pricing updated successfully.']);
    }

    public function updateManualFunding(Request $request)
    {
        $request->validate([
            'bank_name'      => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'account_name'   => 'required|string|max:100',
        ]);

        $existingId = DB::table('manual_funding')->min('id') ?? 1;
        DB::table('manual_funding')->updateOrInsert(
            ['id' => $existingId],
            [
                'bank_name'      => $request->bank_name,
                'account_number' => $request->account_number,
                'account_name'   => $request->account_name,
            ]
        );

        return response()->json(['status' => true, 'message' => 'Payment details updated successfully.']);
    }

    public function updateApiSettings(Request $request)
    {
        $request->validate([
            'nin_search_type'  => 'required|string|max:100',
            'bvn_search_type'  => 'required|string|max:100',
            'data_api_type'    => 'required|string|max:100',
            'airtime_api_type' => 'nullable|string|max:100',
        ]);

        DB::table('api_settings')->updateOrInsert(
            ['id' => DB::table('api_settings')->min('id') ?? 1],
            [
                'nin_search_type'  => $request->nin_search_type,
                'bvn_search_type'  => $request->bvn_search_type,
                'data_api_type'    => $request->data_api_type,
                'airtime_api_type' => $request->airtime_api_type,
                'date'             => now()->toDateString(),
            ]
        );

        return response()->json(['status' => true, 'message' => 'API settings updated successfully.']);
    }

    public function updateApiKeys(Request $request)
    {
        $allowed = [
            'dataverify_api_key',
            'dataverify_endpoint_nin',
            'dataverify_endpoint_phone',
            'dataverify_endpoint_tid',
            'dataverify_endpoint_premium_slip',
            'dataverify_endpoint_premium_slip_phone',
            'dataverify_endpoint_standard_slip',
            'dataverify_endpoint_regular_slip',
            'dataverify_endpoint_vnin_slip',
            'payvessel_api_key', 'payvessel_secret_key', 'payvessel_businessid', 'payvessel_endpoint',
            'monnify_api_key', 'monnify_secret_key', 'monnify_contract_code', 'monnify_endpoint_auth', 'monnify_endpoint_reserve',
            'paystack_public_key', 'paystack_secret_key',
            'flutterwave_public_key', 'flutterwave_secret_key', 'flutterwave_encryption_key',
            'ade_apikey',
            'nexus_notary_key', 'nexus_logistics_key', 'nexus_api_secret',
            'robosttech_api_key',
            'robosttech_endpoint_nin',
            'robosttech_endpoint_validation',
            'robosttech_endpoint_clearance',
            'robosttech_endpoint_clearance_status',
            'robosttech_endpoint_personalization',
            'gemini_api_key',
            'clubkonnect_userid',
            'clubkonnect_apikey',
            'sms_ai_key',
            'sms_ai_endpoint',
            'sms_ai_sender',
        ];

        $vuvaaKeys = [
            'vuvaa_username',
            'vuvaa_password',
            'vuvaa_encryption_key',
            'vuvaa_encryption_iv',
        ];

        $data = array_filter(
            $request->only($allowed),
            fn ($value) => $value !== null && $value !== ''
        );

        $vuvaaData = array_filter(
            $request->only($vuvaaKeys),
            fn ($value) => $value !== null && $value !== ''
        );

        if (empty($data) && empty($vuvaaData) && !$request->has('dataverify_use_phone_slip_for_phone_mode')) {
            return response()->json(['status' => true, 'message' => 'No API keys were updated.']);
        }

        if (!empty($data)) {
            DB::table('api_centers')->updateOrInsert(
                ['id' => DB::table('api_centers')->min('id') ?? 1],
                $data
            );
        }

        if (!empty($vuvaaData)) {
            foreach ($vuvaaData as $k => $v) {
                SystemSetting::set($k, (string) $v, 'integrations');
            }
        }

        // Optional toggle: use phone slip endpoint for phone verifications
        if ($request->has('dataverify_use_phone_slip_for_phone_mode')) {
            SystemSetting::set(
                'dataverify_use_phone_slip_for_phone_mode',
                $request->boolean('dataverify_use_phone_slip_for_phone_mode') ? 'true' : 'false',
                'integrations'
            );
        }

        return response()->json(['status' => true, 'message' => 'API keys updated successfully.']);
    }

    public function updateNotaryDocs(Request $request)
    {
        $request->validate([
            'document_type' => 'required|string|max:100',
            'price'         => 'required|numeric|min:0',
            'description'   => 'nullable|string|max:255',
            'requires_court_stamp' => 'nullable|boolean',
        ]);

        DB::table('notary_settings')->updateOrInsert(
            ['document_type' => $request->document_type],
            [
                'price' => $request->price,
                'description' => $request->description,
                'requires_court_stamp' => $request->has('requires_court_stamp'),
                'updated_at' => now(),
            ]
        );

        return response()->json(['status' => true, 'message' => 'Notary document updated successfully.']);
    }

    public function toggleGateway(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:payment_gateways,id',
            'is_active' => 'required|boolean',
        ]);

        $admin = Auth::guard('admin')->user();
        $isSuperAdmin = false;
        if ($admin) {
            $isSuperAdmin = (($admin->is_super_admin ?? false) || (($admin->role ?? null) === 'superadmin')) || Admin::count() <= 1;
        }

        if (!$isSuperAdmin) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $gateway = \App\Models\PaymentGateway::findOrFail($request->id);
        $gateway->update(['is_active' => $request->is_active]);

        $status = $request->is_active ? 'activated' : 'deactivated';
        Log::info('Payment gateway ' . $gateway->name . ' was ' . $status . ' by admin ID: ' . (Auth::guard('admin')->id() ?? 'unknown'));

        return response()->json([
            'status' => true,
            'message' => $gateway->display_name . ' has been successfully ' . $status . '.',
            'is_active' => (bool) $gateway->is_active,
        ]);
    }

    public function updateBranding(Request $request)
    {
        $request->validate([
            'site_name' => 'nullable|string|max:100',
            'contact_email' => 'nullable|email|max:100',
            'contact_phone' => 'nullable|string|max:20',
            'contact_address' => 'nullable|string|max:255',
            'site_logo' => 'nullable|image|max:2048',
            'site_favicon' => 'nullable|image|max:1024',
            'seo_title' => 'nullable|string|max:150',
            'seo_description' => 'nullable|string|max:500',
            'seo_keywords' => 'nullable|string|max:255',
            'stamp'     => 'nullable|image|max:2048',
            'signature' => 'nullable|string|max:100',
        ]);

        if ($request->has('site_name')) {
            \App\Models\SystemSetting::set('site_name', $request->site_name, 'branding');
        }
        if ($request->has('contact_email')) {
            \App\Models\SystemSetting::set('contact_email', $request->contact_email, 'branding');
        }
        if ($request->has('contact_phone')) {
            \App\Models\SystemSetting::set('contact_phone', $request->contact_phone, 'branding');
        }
        if ($request->has('contact_address')) {
            \App\Models\SystemSetting::set('contact_address', $request->contact_address, 'branding');
        }

        if ($request->has('seo_title')) {
            \App\Models\SystemSetting::set('seo_title', $request->seo_title, 'seo');
        }
        if ($request->has('seo_description')) {
            \App\Models\SystemSetting::set('seo_description', $request->seo_description, 'seo');
        }
        if ($request->has('seo_keywords')) {
            \App\Models\SystemSetting::set('seo_keywords', $request->seo_keywords, 'seo');
        }

        if ($request->hasFile('site_logo')) {
            $url = ImageOptimizer::storeOptimizedPng($request->file('site_logo'), 'public', 'branding', 512, 512);
            if (!$url) {
                $path = $request->file('site_logo')->store('branding', 'public');
                $url = \Illuminate\Support\Facades\Storage::url($path);
            }
            \App\Models\SystemSetting::set('site_logo_url', $url, 'assets');
        }

        if ($request->hasFile('site_favicon')) {
            $url = ImageOptimizer::storeOptimizedPng($request->file('site_favicon'), 'public', 'branding', 64, 64);
            if (!$url) {
                $path = $request->file('site_favicon')->store('branding', 'public');
                $url = \Illuminate\Support\Facades\Storage::url($path);
            }
            \App\Models\SystemSetting::set('site_favicon_url', $url, 'assets');
        }

        if ($request->hasFile('stamp')) {
            $path = $request->file('stamp')->store('stamps', 'public');
            \App\Models\SystemSetting::set('default_stamp_url', \Illuminate\Support\Facades\Storage::url($path), 'assets');
        }

        if ($request->has('signature')) {
            \App\Models\SystemSetting::set('default_signature_prefix', $request->signature, 'assets');
        }

        return response()->json(['status' => true, 'message' => 'Branding and SEO assets updated successfully.']);
    }

    public function updateSystemPricing(Request $request)
    {
        $request->validate([
            'pricing' => 'required|array',
        ]);

        foreach ($request->pricing as $key => $value) {
            \App\Models\SystemSetting::set($key, $value, 'pricing');
        }

        return response()->json(['status' => true, 'message' => 'System pricing updated successfully.']);
    }

    public function updateTheme(Request $request)
    {
        $request->validate([
            'theme_primary' => ['nullable', 'string', 'max:20', 'regex:/^#([0-9a-fA-F]{6})$/'],
            'theme_primary_hover' => ['nullable', 'string', 'max:20', 'regex:/^#([0-9a-fA-F]{6})$/'],
            'theme_accent_1' => ['nullable', 'string', 'max:20', 'regex:/^#([0-9a-fA-F]{6})$/'],
            'theme_accent_2' => ['nullable', 'string', 'max:20', 'regex:/^#([0-9a-fA-F]{6})$/'],
            'theme_accent_3' => ['nullable', 'string', 'max:20', 'regex:/^#([0-9a-fA-F]{6})$/'],
        ]);

        $fields = [
            'theme_primary',
            'theme_primary_hover',
            'theme_accent_1',
            'theme_accent_2',
            'theme_accent_3',
        ];

        foreach ($fields as $field) {
            $value = $request->input($field);
            if ($value !== null && $value !== '') {
                \App\Models\SystemSetting::set($field, $value, 'theme');
            }
        }

        return response()->json(['status' => true, 'message' => 'Theme updated successfully.']);
    }

    public function updateAdminSecurity(Request $request)
    {
        $request->validate([
            'self_funding_limit' => 'required|numeric|min:0',
        ]);

        SystemSetting::set('self_funding_limit', $request->self_funding_limit, 'security');

        $admin = Auth::guard('admin')->user();
        AdminAuditLog::create([
            'admin_id' => $admin?->id,
            'action' => 'security.admin.funding_limit.updated',
            'meta' => [
                'new_limit' => $request->self_funding_limit,
            ],
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);

        return response()->json(['status' => true, 'message' => 'Admin security settings updated successfully.']);
    }

    public function updateVerifymeWebhookIps(Request $request)
    {
        $raw = (string) $request->input('verifyme_webhook_ips', '');
        $tokens = preg_split('/[\s,]+/', $raw) ?: [];
        $ips = collect($tokens)
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->values()
            ->all();

        foreach ($ips as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid IP address: ' . $ip,
                ], 422);
            }
        }

        $old = (string) SystemSetting::get('verifyme_webhook_ips', '');
        $oldIps = collect(preg_split('/[\s,]+/', $old) ?: [])
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $newValue = implode(', ', $ips);
        SystemSetting::put('verifyme_webhook_ips', $newValue, 'security', 'string', 'VerifyMe webhook IP allowlist');

        $admin = Auth::guard('admin')->user();
        AdminAuditLog::create([
            'admin_id' => $admin?->id,
            'action' => 'security.verifyme.ips.updated',
            'meta' => [
                'old_count' => count($oldIps),
                'new_count' => count($ips),
                'added' => array_values(array_diff($ips, $oldIps)),
                'removed' => array_values(array_diff($oldIps, $ips)),
            ],
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);

        return response()->json(['status' => true, 'message' => 'VerifyMe IP allowlist updated.']);
    }

    public function generateVerifymeWebhookSecret(Request $request)
    {
        $secret = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        return response()->json(['status' => true, 'secret' => $secret]);
    }

    public function updateVerifymeWebhookSecret(Request $request)
    {
        $request->validate([
            'verifyme_webhook_secret' => ['required', 'string', 'min:16', 'max:200'],
        ]);

        $oldSet = (bool) SystemSetting::get('verifyme_webhook_secret', '');
        SystemSetting::put('verifyme_webhook_secret', $request->verifyme_webhook_secret, 'security', 'string', 'VerifyMe webhook signature secret');

        $admin = Auth::guard('admin')->user();
        AdminAuditLog::create([
            'admin_id' => $admin?->id,
            'action' => 'security.verifyme.secret.rotated',
            'meta' => [
                'old_set' => $oldSet,
                'new_set' => true,
            ],
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);

        return response()->json(['status' => true, 'message' => 'VerifyMe webhook secret updated.']);
    }

    public function updateFeatureToggles(Request $request)
    {
        $features = [
            'bvn_service_enabled',
            'nin_service_enabled',
            'legal_service_enabled',
            'auction_service_enabled',
            'logistics_service_enabled',
            'airtime_data_enabled',
            'education_service_enabled',
            'insurance_service_enabled',
            'maintenance_mode',
        ];

        foreach ($features as $feature) {
            $value = $request->has($feature) ? 'true' : 'false';
            SystemSetting::set($feature, $value, 'features');
        }

        return response()->json(['status' => true, 'message' => 'Feature toggles updated successfully.']);
    }

    public function updatePaymentGateways(Request $request)
    {
        $request->validate([
            'gateways' => 'required|array',
            'gateways.*.id' => 'required|exists:payment_gateways,id',
            'gateways.*.is_active' => 'boolean',
            'gateways.*.priority' => 'integer|min:0',
            'gateways.*.config' => 'nullable|array',
        ]);

        foreach ($request->gateways as $data) {
            $gateway = \App\Models\PaymentGateway::find($data['id']);
            $gateway->update([
                'is_active' => $data['is_active'] ?? false,
                'priority' => $data['priority'] ?? 0,
                'config' => isset($data['config']) ? array_merge($gateway->config ?? [], $data['config']) : $gateway->config,
            ]);
        }

        return response()->json(['status' => true, 'message' => 'Payment gateways updated successfully.']);
    }

    public function updateReferralSettings(Request $request)
    {
        $request->validate([
            'referral_reward_amount' => 'required|numeric|min:0',
        ]);

        \App\Models\SystemSetting::set(
            'referral_reward_enabled',
            $request->has('referral_reward_enabled') ? 'true' : 'false',
            'referrals'
        );

        \App\Models\SystemSetting::set(
            'referral_reward_amount',
            $request->referral_reward_amount,
            'referrals'
        );
        
        if ($request->has('matrix_enabled')) {
            SystemSetting::set('matrix_enabled', $request->has('matrix_enabled') ? 'true' : 'false', 'referrals');
            SystemSetting::set('matrix_depth', $request->input('matrix_depth', 0), 'referrals');
            
            $depth = (int)$request->input('matrix_depth', 0);
            for ($i = 1; $i <= $depth; $i++) {
                $key = 'matrix_level_' . $i . '_percentage';
                if ($request->has($key)) {
                    SystemSetting::set($key, $request->input($key), 'referrals');
                }
            }
        }

        return response()->json(['status' => true, 'message' => 'Referral settings updated successfully.']);
    }

    public function updateKycSettings(Request $request)
    {
        $request->validate([
            'kyc_enabled' => 'required|boolean',
        ]);

        SystemSetting::set('kyc_enabled', $request->kyc_enabled ? 'true' : 'false', 'kyc');

        return response()->json(['status' => true, 'message' => 'KYC settings updated successfully.']);
    }

    public function updateAuctionSettings(Request $request)
    {
        $request->validate([
            'auction_commission_percentage' => 'required|numeric|min:0|max:100',
        ]);

        SystemSetting::set('auction_commission_percentage', $request->auction_commission_percentage, 'auction');

        return response()->json(['status' => true, 'message' => 'Auction settings updated successfully.']);
    }
}
