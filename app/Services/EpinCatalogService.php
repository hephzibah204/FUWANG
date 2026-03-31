<?php

namespace App\Services;

class EpinCatalogService
{
    public function all(): array
    {
        $products = config('epin_products.products', []);
        return is_array($products) ? $products : [];
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

