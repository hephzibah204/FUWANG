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
        if (!Schema::hasTable('price_list')) {
            return collect();
        }
        $provider = $this->dataProvider($providerId);
        if (($providerId && !$provider) || (!$provider && CustomApi::where('service_type', 'vtu_data')->where('provider_identifier', 'datasub247')->exists())) {
            return collect();
        }
        $hasProvCol = Schema::hasColumn('price_list', 'provider_plan_id');
        $hasApiCol = Schema::hasColumn('price_list', 'custom_api_id');

        $query = DB::table('price_list')->orderBy('amount');
        if ($hasProvCol) {
            $query->whereNotNull('provider_plan_id');
        }
        $rows = $query->get();

        // Check if there are explicit plans assigned to this provider
        $hasExplicit = $provider && $hasApiCol && $rows->contains(fn ($p) => (int) ($p->custom_api_id ?? 0) === (int) $provider->id);

        return $rows->filter(function ($plan) use ($provider, $hasProvCol, $hasApiCol, $hasExplicit) {
            if ((float) $plan->amount < 50) {
                return false;
            }
            if ($hasProvCol && $plan->provider_plan_id !== null && trim((string) $plan->provider_plan_id) === '') {
                return false;
            }
            if ($hasExplicit) {
                return (int) ($plan->custom_api_id ?? 0) === (int) $provider->id;
            }
            if ($provider && $hasApiCol && !empty($plan->custom_api_id)) {
                return (int) $plan->custom_api_id === (int) $provider->id;
            }
            return true;
        })->map(function ($plan) use ($hasProvCol) {
            $plan->network = $this->dataNetwork((string) $plan->network);
            if (!$hasProvCol || empty($plan->provider_plan_id)) {
                $plan->provider_plan_id = (string) $plan->id;
            }
            return $plan;
        })->values();
    }

    private function dataNetwork(string $network): string
    {
        return match (strtoupper(trim($network))) {
            '1', '01', 'MTN' => 'MTN', '2', '02', 'GLO' => 'GLO',
            '3', '04', 'AIRTEL' => 'AIRTEL', '4', '03', 'ETISALAT', '9MOBILE' => '9MOBILE',
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
                return ((string) $p->id === $planId || (string) ($p->provider_plan_id ?? '') === $planId)
                    && $p->network === $net;
            });

            if (!$plan && Schema::hasTable('price_list')) {
                $candidate = DB::table('price_list')
                    ->where(function ($q) use ($planId) {
                        $q->where('id', $planId);
                        if (Schema::hasColumn('price_list', 'provider_plan_id')) {
                            $q->orWhere('provider_plan_id', $planId);
                        }
                    })->first();

                if ($candidate && $this->dataNetwork((string) $candidate->network) === $net) {
                    $candidate->network = $net;
                    if (!isset($candidate->provider_plan_id) || empty($candidate->provider_plan_id)) {
                        $candidate->provider_plan_id = (string) $candidate->id;
                    }
                    $plan = $candidate;
                }
            }

            if (!$plan || $plan->network !== $net) {
                throw ValidationException::withMessages(['plan_id' => 'Select an available data plan for this network and band.']);
            }
            $payload['network'] = $plan->network;
            $payload['catalog_plan_id'] = (string) $plan->id;
            $payload['plan_id'] = !empty($plan->provider_plan_id) ? trim((string) $plan->provider_plan_id) : (string) $plan->id;
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
