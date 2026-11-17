<?php

namespace App\Services;

use App\Models\CustomApi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DatasubDataCatalogueImport
{
    public function provider(): CustomApi
    {
        if (!app(VtuCatalogService::class)->schemaReady()) {
            throw ValidationException::withMessages(['catalogue' => 'Data catalogue setup is incomplete. Run the provider mapping database migration before saving bundles.']);
        }
        $provider = app(VtuProviderService::class)->providers('vtu_data')->first();
        if ($provider) return $provider;
        if (CustomApi::where('service_type', 'vtu_data')->where('provider_identifier', 'datasub247')->exists()
            || !app(Datasub247Service::class)->getApiKey()) {
            throw ValidationException::withMessages(['catalogue' => 'Configure and enable Datasub247 credentials before saving bundles.']);
        }
        // Global Datasub credentials also support data; no separate API selection
        // or duplicated token is needed to configure its catalogue.
        return CustomApi::create(['name' => 'Datasub247', 'service_type' => 'vtu_data',
            'provider_identifier' => 'datasub247', 'status' => true, 'priority' => 1,
            'endpoint' => rtrim(config('services.datasub247.base_url'), '/').'/api/data']);
    }

    public function import(array $plans): int
    {
        $plans = array_map(function ($plan) {
            if (!is_array($plan)) return $plan;
            foreach (['network', 'data_plan', 'provider_plan_id', 'validate'] as $key) {
                if (isset($plan[$key]) && is_scalar($plan[$key])) $plan[$key] = trim((string) $plan[$key]);
            }
            if (is_string($plan['network'] ?? null)) $plan['network'] = strtoupper($plan['network']);
            return $plan;
        }, $plans);
        Validator::make(['plans' => $plans], [
            'plans' => 'required|array|min:1|max:1000',
            'plans.*' => 'required|array',
            'plans.*.network' => 'required|in:MTN,GLO,AIRTEL,9MOBILE',
            'plans.*.data_plan' => 'required|string|max:255',
            'plans.*.provider_plan_id' => 'required|string|regex:/^[1-9][0-9]*$/|max:100',
            'plans.*.amount' => 'required|numeric|min:50|max:500000|decimal:0,2',
            'plans.*.validate' => 'required|string|max:100',
        ])->validate();
        $seen = [];
        foreach ($plans as $plan) {
            $key = $plan['network'].':'.$plan['provider_plan_id'];
            if (isset($seen[$key])) throw ValidationException::withMessages(['catalogue' => 'Each network and provider plan ID must appear only once.']);
            $seen[$key] = true;
        }
        return DB::transaction(function () use ($plans) {
            $provider = $this->provider();
            foreach ($plans as $plan) {
                $existing = DB::table('price_list')->where('custom_api_id', $provider->id)
                    ->where('network', $plan['network'])->where('provider_plan_id', $plan['provider_plan_id'])->first();
                $values = array_intersect_key($plan, array_flip(['network', 'data_plan', 'provider_plan_id', 'amount', 'validate']))
                    + ['custom_api_id' => $provider->id, 'updated_at' => now()];
                if ($existing) DB::table('price_list')->where('id', $existing->id)->update($values);
                else DB::table('price_list')->insert($values + ['created_at' => now()]);
            }
            return count($plans);
        });
    }
}
