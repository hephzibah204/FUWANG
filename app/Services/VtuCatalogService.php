<?php

namespace App\Services;

use App\Models\SystemSetting;
use App\Models\CustomApi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class VtuCatalogService
{
    public function dataProviders(): \Illuminate\Support\Collection
    {
        return app(VtuProviderService::class)->providers('vtu_data');
    }

    public function dataProvider(?int $providerId): ?CustomApi
    {
        $providers = $this->dataProviders();
        return $providerId ? $providers->firstWhere('id', $providerId) : $providers->first();
    }

    public function dataPlans(?int $providerId = null): \Illuminate\Support\Collection
    {
        if (!Schema::hasTable('price_list') || !Schema::hasColumn('price_list', 'provider_plan_id') || !Schema::hasColumn('price_list', 'custom_api_id')) return collect();
        $provider = $this->dataProvider($providerId);
        if (($providerId && !$provider) || (!$provider && CustomApi::where('service_type', 'vtu_data')->where('provider_identifier', 'datasub247')->exists())) return collect();
        return DB::table('price_list')->whereNotNull('provider_plan_id')->orderBy('amount')->get()
            ->filter(fn ($plan) => ctype_digit(trim((string) $plan->provider_plan_id)) && (int) $plan->provider_plan_id > 0
                && (float) $plan->amount >= 50
                && ($provider ? (int) $plan->custom_api_id === (int) $provider->id : empty($plan->custom_api_id)))
            ->map(function ($plan) {
                $plan->network = $this->dataNetwork((string) $plan->network);
                return $plan;
            })->values();
    }

    private function dataNetwork(string $network): string
    {
        return match (strtoupper(trim($network))) {
            '1', '01', 'MTN' => 'MTN', '3', '02', 'GLO' => 'GLO',
            '2', '04', 'AIRTEL' => 'AIRTEL', '4', '03', 'ETISALAT', '9MOBILE' => '9MOBILE',
            default => strtoupper(trim($network)),
        };
    }
    public function products(string $serviceType): array
    {
        $catalog = SystemSetting::get('vtu_product_catalog', config('vtu_services.products', []));
        if (is_string($catalog)) {
            $catalog = json_decode($catalog, true) ?: [];
        }
        $providerId = app(VtuProviderService::class)->providers($serviceType)->first()?->id;
        return array_values(array_filter((array) $catalog, fn ($p) => is_array($p)
            && ($p['service_type'] ?? '') === $serviceType && ($p['active'] ?? true)
            && (empty($p['provider_id']) || (int) $p['provider_id'] === (int) $providerId)));
    }

    public function price(string $serviceType, array &$payload, ?int $providerId): float
    {
        if ($serviceType === 'vtu_data') {
            $planId = (string) ($payload['plan_id'] ?? '');
            $net = $this->dataNetwork((string) ($payload['network'] ?? ''));
            $plan = $this->dataPlans($providerId)->first(function ($p) use ($planId, $net) {
                if ($p->network !== $net) return false;
                return (string) $p->id === $planId;
            });

            if (!$plan || $plan->network !== $net) {
                throw ValidationException::withMessages(['plan_id' => 'Select an available data plan for this network and band.']);
            }
            $payload['network'] = $plan->network;
            $payload['catalog_plan_id'] = (string) $plan->id;
            $payload['plan_id'] = trim((string) $plan->provider_plan_id);
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
