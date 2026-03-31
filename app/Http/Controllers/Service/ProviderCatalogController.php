<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Models\CustomApi;
use Illuminate\Http\Request;

class ProviderCatalogController extends Controller
{
    public function types(Request $request, int $providerId)
    {
        $serviceType = $request->query('service_type');

        $provider = CustomApi::query()
            ->where('id', $providerId)
            ->where('status', true)
            ->firstOrFail();

        if ($serviceType && $provider->service_type !== $serviceType) {
            abort(404);
        }

        $types = $provider->verificationTypes()
            ->where('status', true)
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get(['id', 'type_key', 'label', 'price']);

        return response()->json([
            'status' => true,
            'provider' => [
                'id' => $provider->id,
                'name' => $provider->name,
                'service_type' => $provider->service_type,
                'price' => (float) $provider->price,
                'timeout_seconds' => (int) ($provider->timeout_seconds ?? 60),
                'retry_count' => (int) ($provider->retry_count ?? 0),
                'retry_delay_ms' => (int) ($provider->retry_delay_ms ?? 0),
            ],
            'types' => $types->map(fn ($t) => [
                'id' => $t->id,
                'key' => $t->type_key,
                'label' => $t->label,
                'price' => (float) $t->price,
            ])->values(),
        ]);
    }
}

