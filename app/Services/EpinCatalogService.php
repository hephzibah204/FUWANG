<?php

namespace App\Services;

class EpinCatalogService
{
    public function all(): array
    {
        $products = config('epin_products.products', []);
        if (!is_array($products)) { return []; }
        $rates = ['waec_result_checker' => ['vtu_waec_price', 3800], 'waec_registration_pin' => ['vtu_waec_reg_price', 18500],
            'neco_result_checker' => ['vtu_neco_price', 1500], 'nabteb_result_checker' => ['vtu_nabteb_price', 1200], 'jamb_profile_pin' => ['vtu_jamb_price', 4700]];
        foreach ($rates as $key => [$setting, $default]) {
            if (isset($products[$key])) { $products[$key]['amount'] = (float) \App\Models\SystemSetting::get($setting, $default); }
        }
        return $products;
    }

    public function findByKey(string $key): ?array
    {
        $all = $this->all();
        $p = $all[$key] ?? null;
        return is_array($p) ? $p : null;
    }

    public function findByService(string $serviceId, string $variationCode): ?array
    {
        foreach ($this->all() as $p) {
            if (!is_array($p)) {
                continue;
            }
            if (($p['service_id'] ?? null) === $serviceId && ($p['variation_code'] ?? null) === $variationCode) {
                return $p;
            }
        }
        return null;
    }
}

