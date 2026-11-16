<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('custom_apis')) {
            return;
        }

        $services = [
            [
                'name' => 'Datasub247 Airtime',
                'service_type' => 'vtu_airtime',
                'endpoint' => 'https://datasub247.com/api/topup',
            ],
            [
                'name' => 'Datasub247 Data Bundles',
                'service_type' => 'vtu_data',
                'endpoint' => 'https://datasub247.com/api/data',
            ],
            [
                'name' => 'Datasub247 Cable TV',
                'service_type' => 'vtu_cable_tv',
                'endpoint' => 'https://datasub247.com/api/cable',
            ],
            [
                'name' => 'Datasub247 Electricity Bills',
                'service_type' => 'vtu_electricity',
                'endpoint' => 'https://datasub247.com/api/bill',
            ],
            [
                'name' => 'Datasub247 Exam PINs',
                'service_type' => 'vtu_epin',
                'endpoint' => 'https://datasub247.com/api/exam',
            ],
            [
                'name' => 'Datasub247 Bulk SMS',
                'service_type' => 'vtu_bulksms',
                'endpoint' => 'https://datasub247.com/api/bulksms',
            ],
            [
                'name' => 'Datasub247 Recharge Card Printing',
                'service_type' => 'vtu_recharge_card',
                'endpoint' => 'https://datasub247.com/api/recharge_card',
            ],
            [
                'name' => 'Datasub247 Data Card Printing',
                'service_type' => 'vtu_data_card',
                'endpoint' => 'https://datasub247.com/api/data_card',
            ],
        ];

        foreach ($services as $svc) {
            $existing = DB::table('custom_apis')
                ->where('provider_identifier', 'datasub247')
                ->where('service_type', $svc['service_type'])
                ->first();

            $data = [
                'name' => $svc['name'],
                'provider_identifier' => 'datasub247',
                'service_type' => $svc['service_type'],
                'supported_modes' => json_encode(['live', 'sandbox']),
                'endpoint' => $svc['endpoint'],
                'api_key' => env('DATASUB247_API_KEY'),
                'headers' => json_encode(['Content-Type' => 'application/json']),
                'config' => json_encode(['base_url' => env('DATASUB247_BASE_URL', 'https://datasub247.com')]),
                'status' => 1,
                'priority' => 1,
                'timeout_seconds' => 45,
                'retry_count' => 2,
                'retry_delay_ms' => 1000,
                'updated_at' => now(),
            ];

            if ($existing) {
                DB::table('custom_apis')
                    ->where('id', $existing->id)
                    ->update($data);
            } else {
                $data['created_at'] = now();
                DB::table('custom_apis')->insert($data);
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('custom_apis')) {
            return;
        }

        DB::table('custom_apis')
            ->where('provider_identifier', 'datasub247')
            ->delete();
    }
};
