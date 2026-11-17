<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('price_list') || !Schema::hasColumn('price_list', 'provider_plan_id')
            || !Schema::hasColumn('price_list', 'custom_api_id') || !Schema::hasTable('custom_apis')) {
            throw new RuntimeException('Run the VTU catalogue and provider mapping migrations before loading Datasub plans.');
        }
        DB::transaction(function () {
            $provider = DB::table('custom_apis')->where('service_type', 'vtu_data')
                ->where('provider_identifier', 'datasub247')->orderByDesc('status')->orderBy('priority')->orderBy('id')->first();
            $providerId = $provider?->id ?? DB::table('custom_apis')->insertGetId([
                'name' => 'Datasub247', 'service_type' => 'vtu_data', 'provider_identifier' => 'datasub247',
                'endpoint' => 'https://datasub247.com/api/data', 'status' => true, 'priority' => 1,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            foreach (require base_path('config/datasub247_data.php') as $plan) {
                $exists = DB::table('price_list')->where('custom_api_id', $providerId)
                    ->where('network', $plan['network'])->where('provider_plan_id', $plan['provider_plan_id'])->exists();
                if (!$exists) DB::table('price_list')->insert($plan + [
                    'custom_api_id' => $providerId, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        // Retain catalogue entries and any prices subsequently edited by admins.
    }
};
