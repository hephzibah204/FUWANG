<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            // This new migration also covers installations that already ran the
            // earlier migrations when only the first table pages were available.
            (require __DIR__.'/2026_11_17_120000_load_documented_datasub_data_plans.php')->up();
            (require __DIR__.'/2026_11_17_121000_load_documented_datasub_cable_and_exam_prices.php')->up();
            $providerIds = DB::table('custom_apis')->where('service_type', 'vtu_data')
                ->where('provider_identifier', 'datasub247')->pluck('id');
            foreach (require base_path('config/datasub247_data.php') as $plan) {
                // Fix inaccurate legacy labels and validity without changing
                // saved selling prices or local IDs used in transaction history.
                DB::table('price_list')->whereIn('custom_api_id', $providerIds)
                    ->where('provider_plan_id', $plan['provider_plan_id'])->update([
                        'network' => $plan['network'], 'data_plan' => $plan['data_plan'],
                        'validate' => $plan['validate'], 'updated_at' => now(),
                    ]);
            }
            // Earlier bundled snapshots contained IDs absent from the complete
            // provider catalogue. Retain their rows/prices for admin review, but
            // unassign them so they cannot be sold through Datasub247.
            $verifiedIds = array_column(require base_path('config/datasub247_data.php'), 'provider_plan_id');
            DB::table('price_list')->whereIn('custom_api_id', $providerIds)
                ->whereNotNull('provider_plan_id')->whereNotIn('provider_plan_id', $verifiedIds)
                ->update(['custom_api_id' => null, 'updated_at' => now()]);
            DB::afterCommit(function () {
                \Illuminate\Support\Facades\Cache::forget('system_settings_all');
                \App\Models\SystemSetting::clearLocalCache();
            });
        });
    }

    public function down(): void
    {
        // Catalogue records and admin pricing are retained.
    }
};
