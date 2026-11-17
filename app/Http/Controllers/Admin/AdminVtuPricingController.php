<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminVtuPricingController extends Controller
{
    /**
     * Display the VTU pricing and data bundle management dashboard.
     */
    public function index()
    {
        // Ensure price_list table exists and seed defaults if empty
        // Products are configured explicitly; opening this page must not recreate deleted plans.

        $bundles = DB::table('price_list')
            ->orderBy('network')
            ->orderBy('amount')
            ->get()
            ->groupBy(function ($item) {
                return strtoupper((string) $item->network);
            });

        $rates = [
            'vtu_airtime_discount_percent' => (float) SystemSetting::get('vtu_airtime_discount_percent', 2.0),
            'vtu_bulk_sms_rate' => (float) SystemSetting::get('vtu_bulk_sms_rate', 4.00),
            'vtu_electricity_fee' => (float) SystemSetting::get('vtu_electricity_fee', 0.0),
            'vtu_waec_price' => (float) SystemSetting::get('vtu_waec_price', 3800.0),
            'vtu_waec_reg_price' => (float) SystemSetting::get('vtu_waec_reg_price', 18500.0),
            'vtu_neco_price' => (float) SystemSetting::get('vtu_neco_price', 1500.0),
            'vtu_nabteb_price' => (float) SystemSetting::get('vtu_nabteb_price', 1200.0),
            'vtu_jamb_price' => (float) SystemSetting::get('vtu_jamb_price', 4700.0),
        ];

        $providers = \App\Models\CustomApi::where('status', true)->where('service_type', 'vtu_data')->orderBy('priority')->get();
        $bandNames = \App\Models\CustomApi::where('service_type', 'vtu_data')->pluck('name', 'id');
        $productCatalog = SystemSetting::get('vtu_product_catalog', '[]');
        return view('admin.vtu.pricing', compact('bundles', 'rates', 'providers', 'productCatalog', 'bandNames'));
    }

    /**
     * Update global rates and discounts (Airtime %, Bulk SMS, Education PINs, Electricity fee).
     */
    public function updateRates(Request $request)
    {
        $request->validate([
            'vtu_airtime_discount_percent' => 'required|numeric|min:0|max:99',
            'product_catalog' => 'nullable|json',
            'vtu_bulk_sms_rate' => 'required|numeric|min:0.5',
            'vtu_electricity_fee' => 'required|numeric|min:0',
            'vtu_waec_price' => 'required|numeric|min:100',
            'vtu_waec_reg_price' => 'required|numeric|min:100',
            'vtu_neco_price' => 'required|numeric|min:100',
            'vtu_nabteb_price' => 'required|numeric|min:100',
            'vtu_jamb_price' => 'required|numeric|min:100',
        ]);

        if ($request->filled('product_catalog')) {
            $products = json_decode($request->product_catalog, true);
            \Illuminate\Support\Facades\Validator::make(['products' => $products], [
                'products' => 'array', 'products.*.service_type' => 'required|in:vtu_cable_tv,vtu_internet,vtu_betting,vtu_data_card,vtu_recharge_card',
                'products.*.label' => 'required|string|max:150', 'products.*.code' => 'required|string|max:100',
                'products.*.amount' => 'required|numeric|min:1|max:500000', 'products.*.provider_plan_id' => 'nullable|string|max:100',
                'products.*.service_id' => 'nullable|string|max:100', 'products.*.network' => 'nullable|in:MTN,GLO,AIRTEL,9MOBILE',
                'products.*.provider_id' => 'required|integer|exists:custom_apis,id',
            ])->validate();
            foreach ($products as $product) {
                $provider = \App\Models\CustomApi::findOrFail($product['provider_id']);
                $aliases = ['vtu_cable_tv' => 'cable_tv'];
                if (!in_array($provider->service_type, [$product['service_type'], $aliases[$product['service_type']] ?? ''], true)) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['product_catalog' => 'Product band does not support its service.']);
                }
            }
            SystemSetting::set('vtu_product_catalog', $products, 'vtu_pricing');
        }
        $keys = [
            'vtu_airtime_discount_percent',
            'vtu_bulk_sms_rate',
            'vtu_electricity_fee',
            'vtu_waec_price',
            'vtu_waec_reg_price',
            'vtu_neco_price',
            'vtu_nabteb_price',
            'vtu_jamb_price',
        ];

        foreach ($keys as $key) {
            SystemSetting::set($key, (float) $request->input($key), 'vtu_pricing');
        }

        if ($request->wantsJson()) {
            return response()->json(['status' => true, 'message' => 'VTU rates and discounts updated successfully.']);
        }

        return back()->with('success', 'VTU rates and discounts updated successfully.');
    }

    /**
     * Store a new data bundle.
     */
    public function storeBundle(Request $request)
    {
        $request->validate([
            'network' => 'required|string|in:MTN,GLO,AIRTEL,9MOBILE',
            'data_plan' => 'required|string|max:255',
            'provider_plan_id' => 'required|string|max:100',
            'custom_api_id' => 'required|integer|exists:custom_apis,id',
            'amount' => 'required|numeric|min:50',
            'validate' => 'nullable|string|max:100',
        ]);

        if (!\App\Models\CustomApi::whereKey($request->custom_api_id)->where('service_type', 'vtu_data')->where('status', true)->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['custom_api_id' => 'Choose an active data band.']);
        }
        DB::table('price_list')->insert([
            'network' => strtoupper($request->network),
            'data_plan' => trim($request->data_plan),
            'provider_plan_id' => trim($request->provider_plan_id),
            'custom_api_id' => (int) $request->custom_api_id,
            'amount' => (float) $request->amount,
            'validate' => trim((string) ($request->validate ?: '30 Days')),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['status' => true, 'message' => 'Data bundle created successfully.']);
        }

        return back()->with('success', 'Data bundle created successfully.');
    }

    /**
     * Update an existing data bundle.
     */
    public function updateBundle(Request $request, $id)
    {
        $request->validate([
            'network' => 'required|string|in:MTN,GLO,AIRTEL,9MOBILE',
            'data_plan' => 'required|string|max:255',
            'provider_plan_id' => 'required|string|max:100',
            'custom_api_id' => 'required|integer|exists:custom_apis,id',
            'amount' => 'required|numeric|min:50',
            'validate' => 'nullable|string|max:100',
        ]);

        if (!\App\Models\CustomApi::whereKey($request->custom_api_id)->where('service_type', 'vtu_data')->where('status', true)->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['custom_api_id' => 'Choose an active data band.']);
        }
        DB::table('price_list')->where('id', $id)->update([
            'network' => strtoupper($request->network),
            'data_plan' => trim($request->data_plan),
            'provider_plan_id' => trim($request->provider_plan_id),
            'custom_api_id' => (int) $request->custom_api_id,
            'amount' => (float) $request->amount,
            'validate' => trim((string) ($request->validate ?: '30 Days')),
            'updated_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['status' => true, 'message' => 'Data bundle updated successfully.']);
        }

        return back()->with('success', 'Data bundle updated successfully.');
    }

    /**
     * Delete a data bundle.
     */
    public function deleteBundle(Request $request, $id)
    {
        DB::table('price_list')->where('id', $id)->delete();

        if ($request->wantsJson()) {
            return response()->json(['status' => true, 'message' => 'Data bundle deleted successfully.']);
        }

        return back()->with('success', 'Data bundle deleted successfully.');
    }

    /**
     * Helper to seed standard defaults if table is empty.
     */
    protected function ensureDefaultPriceList(): void
    {
        if (!Schema::hasTable('price_list')) {
            return;
        }

        try {
            if (DB::table('price_list')->count() > 0) {
                return;
            }

            $now = now();
            $defaults = [
                // MTN
                ['network' => 'MTN', 'data_plan' => '500MB SME Data', 'amount' => 145.00, 'validate' => '30 Days'],
                ['network' => 'MTN', 'data_plan' => '1.0GB SME Data', 'amount' => 290.00, 'validate' => '30 Days'],
                ['network' => 'MTN', 'data_plan' => '2.0GB SME Data', 'amount' => 580.00, 'validate' => '30 Days'],
                ['network' => 'MTN', 'data_plan' => '3.0GB SME Data', 'amount' => 870.00, 'validate' => '30 Days'],
                ['network' => 'MTN', 'data_plan' => '5.0GB SME Data', 'amount' => 1450.00, 'validate' => '30 Days'],
                ['network' => 'MTN', 'data_plan' => '10.0GB SME Data', 'amount' => 2900.00, 'validate' => '30 Days'],
                // GLO
                ['network' => 'GLO', 'data_plan' => '1.0GB Corporate Gifting', 'amount' => 260.00, 'validate' => '30 Days'],
                ['network' => 'GLO', 'data_plan' => '2.0GB Corporate Gifting', 'amount' => 520.00, 'validate' => '30 Days'],
                ['network' => 'GLO', 'data_plan' => '3.0GB Corporate Gifting', 'amount' => 780.00, 'validate' => '30 Days'],
                ['network' => 'GLO', 'data_plan' => '5.0GB Corporate Gifting', 'amount' => 1300.00, 'validate' => '30 Days'],
                ['network' => 'GLO', 'data_plan' => '10.0GB Corporate Gifting', 'amount' => 2600.00, 'validate' => '30 Days'],
                // AIRTEL
                ['network' => 'AIRTEL', 'data_plan' => '1.0GB Corporate Gifting', 'amount' => 280.00, 'validate' => '30 Days'],
                ['network' => 'AIRTEL', 'data_plan' => '2.0GB Corporate Gifting', 'amount' => 560.00, 'validate' => '30 Days'],
                ['network' => 'AIRTEL', 'data_plan' => '5.0GB Corporate Gifting', 'amount' => 1400.00, 'validate' => '30 Days'],
                ['network' => 'AIRTEL', 'data_plan' => '10.0GB Corporate Gifting', 'amount' => 2800.00, 'validate' => '30 Days'],
                // 9MOBILE
                ['network' => '9MOBILE', 'data_plan' => '1.0GB Corporate Gifting', 'amount' => 250.00, 'validate' => '30 Days'],
                ['network' => '9MOBILE', 'data_plan' => '2.0GB Corporate Gifting', 'amount' => 500.00, 'validate' => '30 Days'],
                ['network' => '9MOBILE', 'data_plan' => '5.0GB Corporate Gifting', 'amount' => 1250.00, 'validate' => '30 Days'],
            ];

            foreach ($defaults as &$d) {
                $d['created_at'] = $now;
                $d['updated_at'] = $now;
            }

            DB::table('price_list')->insert($defaults);
        } catch (\Throwable $e) {
            // DB not connected or error during seeding
        }
    }
}
