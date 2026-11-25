<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $provider = DB::table('custom_apis')->whereIn('service_type', ['vtu_cable_tv', 'cable_tv'])
                ->where('provider_identifier', 'datasub247')->orderByDesc('status')->orderBy('priority')->orderBy('id')->first();
            $providerId = $provider?->id ?? DB::table('custom_apis')->insertGetId([
                'name' => 'Datasub247 Cable', 'service_type' => 'vtu_cable_tv', 'provider_identifier' => 'datasub247',
                'endpoint' => 'https://datasub247.com/api/cable', 'status' => true, 'priority' => 1,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $stored = DB::table('system_settings')->where('key', 'vtu_product_catalog')->lockForUpdate()->first();
            $raw = trim((string) ($stored?->value ?? ''));
            $products = $raw !== '' ? json_decode($raw, true, 512, JSON_THROW_ON_ERROR) : [];
            if (!is_array($products) || !array_is_list($products)) throw new RuntimeException('The existing VTU product catalogue must be a JSON array.');
            foreach (require base_path('config/datasub247_cable.php') as $plan) {
                $exists = collect($products)->contains(fn ($p) => is_array($p) && ($p['service_type'] ?? '') === 'vtu_cable_tv'
                    && ($p['service_id'] ?? '') === $plan['service_id'] && (string) ($p['provider_plan_id'] ?? '') === $plan['provider_plan_id']
                    && (int) ($p['provider_id'] ?? 0) === (int) $providerId);
                if (!$exists) $products[] = $plan + ['provider_id' => $providerId];
            }
            // system_settings.id has no AUTO_INCREMENT on production (one of the
            // tables excluded from the 2026-11-24 repair pass pending data
            // reconciliation), so inserts must supply an explicit unique id.
            $nextSettingId = fn () => (int) (DB::table('system_settings')->max('id') ?? 0) + 1;
            $catalogueValues = [
                'value' => json_encode($products, JSON_THROW_ON_ERROR), 'group' => 'vtu_pricing', 'updated_at' => now(),
            ];
            if ($stored) {
                DB::table('system_settings')->where('key', 'vtu_product_catalog')->update($catalogueValues);
            } else {
                DB::table('system_settings')->insert($catalogueValues + [
                    'id' => $nextSettingId(), 'key' => 'vtu_product_catalog', 'created_at' => now(),
                ]);
            }
            foreach (['vtu_waec_price' => 4000, 'vtu_neco_price' => 1450, 'vtu_nabteb_price' => 1100] as $key => $price) {
                if (!DB::table('system_settings')->where('key', $key)->exists()) {
                    DB::table('system_settings')->insert(['id' => $nextSettingId(), 'key' => $key, 'value' => (string) $price, 'group' => 'vtu_pricing', 'created_at' => now(), 'updated_at' => now()]);
                }
            }
        });
        Cache::forget('system_settings_all');
        \App\Models\SystemSetting::clearLocalCache();
    }

    public function down(): void
    {
        // Preserve products and any prices subsequently configured by admins.
    }
};
