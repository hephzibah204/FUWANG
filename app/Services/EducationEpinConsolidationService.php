<?php

namespace App\Services;

use App\Models\CustomApi;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class EducationEpinConsolidationService
{
    public function status(): array
    {
        $flagKey = 'integration.education_epin_v1';
        $hasSystemSettings = Schema::hasTable('system_settings');
        $flag = $hasSystemSettings ? (string) SystemSetting::get($flagKey, '') : '';

        $hasConfig = is_array(config('epin_products.products', null)) && !empty(config('epin_products.products', []));

        $educationTypes = [
            'education_waec',
            'education_waec_registration',
            'education_neco',
            'education_nabteb',
            'education_jamb',
        ];

        $counts = [
            'vtu_epin' => Schema::hasTable('custom_apis') ? CustomApi::where('service_type', 'vtu_epin')->count() : 0,
            'education' => Schema::hasTable('custom_apis') ? CustomApi::whereIn('service_type', $educationTypes)->count() : 0,
        ];

        return [
            'flag_key' => $flagKey,
            'has_system_settings' => $hasSystemSettings,
            'flag_value' => $flag,
            'has_epin_products_config' => $hasConfig,
            'custom_api_counts' => $counts,
            'is_consolidated' => $hasConfig && ($flag === 'true' || !$hasSystemSettings),
        ];
    }

    public function ensureConsolidated(): array
    {
        $s = $this->status();
        if ($s['is_consolidated'] === true) {
            return $s;
        }

        if ($s['has_system_settings'] !== true) {
            return $s;
        }

        SystemSetting::set(
            $s['flag_key'],
            'true',
            'integrations',
            'boolean',
            'Education ↔ ePIN consolidation applied'
        );

        Log::info('Education/ePIN consolidation flag set', [
            'key' => $s['flag_key'],
            'vtu_epin_providers' => $s['custom_api_counts']['vtu_epin'] ?? null,
            'education_providers' => $s['custom_api_counts']['education'] ?? null,
        ]);

        return $this->status();
    }
}

