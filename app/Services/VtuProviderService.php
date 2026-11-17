<?php

namespace App\Services;

use App\Models\CustomApi;
use Illuminate\Support\Collection;

/** Current rollout: only Datasub247 is enabled. Other API records stay saved. */
class VtuProviderService
{
    public function supports(string $type): bool
    {
        return in_array($type, [
            'vtu_airtime', 'vtu_data', 'vtu_cable_tv', 'vtu_electricity',
            'vtu_epin', 'vtu_bulksms', 'vtu_data_card', 'vtu_recharge_card',
            'vtu_internet', 'vtu_airtime_to_cash', 'vtu_betting',
            'education_waec', 'education_neco', 'education_nabteb', 'education_jamb'
        ], true) || CustomApi::whereIn('service_type', match ($type) {
            'vtu_cable_tv' => [$type, 'cable_tv'],
            'vtu_electricity' => [$type, 'electricity_bills'],
            default => [$type],
        })->where('status', true)->exists();
    }

    public function providers(string $type): Collection
    {
        if (!$this->supports($type)) return collect();
        $types = match ($type) {
            'vtu_cable_tv' => [$type, 'cable_tv'],
            'vtu_electricity' => [$type, 'electricity_bills'],
            default => [$type],
        };
        return CustomApi::whereIn('service_type', $types)->where('provider_identifier', 'datasub247')
            ->where('status', true)->orderBy('priority')->orderBy('id')->get()
            ->filter(fn ($p) => trim((string) $p->api_key) !== '' || app(Datasub247Service::class)->getApiKey())
            ->take(1)->values();
    }

    public function listing(string $type): Collection
    {
        $providers = $this->providers($type)->map(fn ($p) => ['id' => $p->id, 'name' => 'Datasub247']);
        if ($providers->isEmpty() && $this->supports($type)
            && !CustomApi::where('provider_identifier', 'datasub247')->whereIn('service_type', match ($type) {
                'vtu_cable_tv' => [$type, 'cable_tv'], 'vtu_electricity' => [$type, 'electricity_bills'], default => [$type],
            })->exists() && app(Datasub247Service::class)->getApiKey()) {
            $providers->push(['id' => '', 'name' => 'Datasub247']);
        }
        return $providers;
    }
}
