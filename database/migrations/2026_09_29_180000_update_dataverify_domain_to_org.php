<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update CustomApi endpoints
        if (Schema::hasTable('custom_apis')) {
            $apis = DB::table('custom_apis')
                ->where('endpoint', 'like', '%dataverify.com.ng%')
                ->orWhere('endpoint', 'like', '%dataverify.ng%')
                ->get();

            foreach ($apis as $api) {
                $newEndpoint = preg_replace(
                    '#https?://(?:api\.)?dataverify\.(?:com\.ng|ng)#i',
                    'https://dataverify.org',
                    $api->endpoint
                );
                DB::table('custom_apis')->where('id', $api->id)->update([
                    'endpoint' => $newEndpoint,
                ]);
            }
        }

        // 2. Update ApiCenter endpoints
        if (Schema::hasTable('api_centers')) {
            $centers = DB::table('api_centers')->get();
            $endpointColumns = [
                'dataverify_endpoint_nin',
                'dataverify_endpoint_bvn',
                'dataverify_endpoint_bvn_retrieval',
                'dataverify_endpoint_bvn_retrieval_status',
                'dataverify_endpoint_data',
                'dataverify_endpoint_phone',
                'dataverify_endpoint_tid',
                'dataverify_endpoint_premium_slip',
                'dataverify_endpoint_premium_slip_phone',
                'dataverify_endpoint_standard_slip',
                'dataverify_endpoint_regular_slip',
                'dataverify_endpoint_vnin_slip',
            ];

            foreach ($centers as $center) {
                $updates = [];
                foreach ($endpointColumns as $col) {
                    if (Schema::hasColumn('api_centers', $col) && !empty($center->$col)) {
                        $updates[$col] = preg_replace(
                            '#https?://(?:api\.)?dataverify\.(?:com\.ng|ng)#i',
                            'https://dataverify.org',
                            $center->$col
                        );
                    }
                }
                if (!empty($updates)) {
                    DB::table('api_centers')->where('id', $center->id)->update($updates);
                }
            }
        }

        // 3. Update SystemSettings if any dataverify endpoints are stored
        if (Schema::hasTable('system_settings')) {
            $settings = DB::table('system_settings')
                ->where('key', 'like', '%dataverify%')
                ->get();

            foreach ($settings as $setting) {
                if (is_string($setting->value) && (str_contains($setting->value, 'dataverify.com.ng') || str_contains($setting->value, 'dataverify.ng'))) {
                    $newValue = preg_replace(
                        '#https?://(?:api\.)?dataverify\.(?:com\.ng|ng)#i',
                        'https://dataverify.org',
                        $setting->value
                    );
                    DB::table('system_settings')->where('id', $setting->id)->update([
                        'value' => $newValue,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('custom_apis')) {
            $apis = DB::table('custom_apis')
                ->where('endpoint', 'like', '%dataverify.org%')
                ->get();

            foreach ($apis as $api) {
                $newEndpoint = str_replace('https://dataverify.org', 'https://dataverify.com.ng', $api->endpoint);
                DB::table('custom_apis')->where('id', $api->id)->update([
                    'endpoint' => $newEndpoint,
                ]);
            }
        }

        if (Schema::hasTable('api_centers')) {
            $centers = DB::table('api_centers')->get();
            $endpointColumns = [
                'dataverify_endpoint_nin',
                'dataverify_endpoint_bvn',
                'dataverify_endpoint_bvn_retrieval',
                'dataverify_endpoint_bvn_retrieval_status',
                'dataverify_endpoint_data',
                'dataverify_endpoint_phone',
                'dataverify_endpoint_tid',
                'dataverify_endpoint_premium_slip',
                'dataverify_endpoint_premium_slip_phone',
                'dataverify_endpoint_standard_slip',
                'dataverify_endpoint_regular_slip',
                'dataverify_endpoint_vnin_slip',
            ];

            foreach ($centers as $center) {
                $updates = [];
                foreach ($endpointColumns as $col) {
                    if (Schema::hasColumn('api_centers', $col) && !empty($center->$col)) {
                        $updates[$col] = str_replace('https://dataverify.org', 'https://dataverify.com.ng', $center->$col);
                    }
                }
                if (!empty($updates)) {
                    DB::table('api_centers')->where('id', $center->id)->update($updates);
                }
            }
        }

        if (Schema::hasTable('system_settings')) {
            $settings = DB::table('system_settings')
                ->where('key', 'like', '%dataverify%')
                ->get();

            foreach ($settings as $setting) {
                if (is_string($setting->value) && str_contains($setting->value, 'dataverify.org')) {
                    $newValue = str_replace('https://dataverify.org', 'https://dataverify.com.ng', $setting->value);
                    DB::table('system_settings')->where('id', $setting->id)->update([
                        'value' => $newValue,
                    ]);
                }
            }
        }
    }
};
