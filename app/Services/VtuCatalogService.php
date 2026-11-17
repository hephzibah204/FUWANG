<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class VtuCatalogService
{
    public function products(string $serviceType): array
    {
        $catalog = SystemSetting::get('vtu_product_catalog', config('vtu_services.products', []));
        if (is_string($catalog)) {
            $catalog = json_decode($catalog, true) ?: [];
        }
        if (!$catalog && in_array($serviceType, ['vtu_data_card', 'vtu_recharge_card'], true) && !app(Datasub247Service::class)->getApiKey()) {
            return app(ClubkonnectPrintingService::class)->products($serviceType);
        }
        return array_values(array_filter((array) $catalog, fn ($p) => is_array($p) && ($p['service_type'] ?? '') === $serviceType && ($p['active'] ?? true)));
    }

    public function price(string $serviceType, array &$payload, ?int $providerId): float
    {
        if ($serviceType === 'vtu_data') {
            $plan = Schema::hasTable('price_list') ? DB::table('price_list')->where('id', $payload['plan_id'] ?? '')->first() : null;
            if (!$plan || strtoupper($plan->network) !== strtoupper($payload['network'] ?? '') || empty($plan->provider_plan_id)
                || (!empty($plan->custom_api_id) && (int) $plan->custom_api_id !== $providerId)) {
                throw ValidationException::withMessages(['plan_id' => 'Select an available data plan for this network and band.']);
            }
            $payload['catalog_plan_id'] = (string) $plan->id;
            $payload['plan_id'] = (string) $plan->provider_plan_id;
            return (float) $plan->amount;
        }
        if ($serviceType === 'vtu_epin') {
            $product = app(EpinCatalogService::class)->findByService($payload['serviceID'] ?? '', $payload['variation_code'] ?? '');
            if (!$product) {
                throw ValidationException::withMessages(['variation_code' => 'Select a configured ePIN product.']);
            }
            return (float) $product['amount'] * (int) ($payload['quantity'] ?? 1);
        }
        $code = (string) ($payload['variation_code'] ?? $payload['plan_type'] ?? '');
        foreach ($this->products($serviceType) as $p) {
            if ((string) ($p['code'] ?? '') !== $code
                || (isset($p['service_id']) && $p['service_id'] !== ($payload['serviceID'] ?? ''))
                || (isset($p['network']) && strtoupper($p['network']) !== strtoupper((string) ($payload['network'] ?? '')))
                || (!empty($p['provider_id']) && (int) $p['provider_id'] !== $providerId)) {
                continue;
            }
            $field = in_array($serviceType, ['vtu_data_card', 'vtu_recharge_card'], true) ? 'plan_type' : 'variation_code';
            $payload[$field] = (string) ($p['provider_plan_id'] ?? $p['code']);
            $payload['product_label'] = $p['label'];
            $payload['unit_amount'] = (float) $p['amount'];
            return (float) $p['amount'] * (int) ($payload['quantity'] ?? 1);
        }
        throw ValidationException::withMessages(['variation_code' => 'This product is unavailable. Please select a configured product.']);
    }
}
