<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PublicServiceController extends Controller
{
    public function index(Request $request)
    {
        $config = (array) config('public_services', []);
        $categories = (array) ($config['categories'] ?? []);
        $services = collect((array) ($config['services'] ?? []));

        // Filter services based on feature toggles
        $services = $services->filter(function($service) {
            $slug = $service['slug'] ?? '';
            if (str_contains($slug, 'nin')) return \App\Models\SystemSetting::get('nin_service_enabled', 'true') === 'true';
            if (str_contains($slug, 'bvn')) return \App\Models\SystemSetting::get('bvn_service_enabled', 'true') === 'true';
            if (str_contains($slug, 'validation') || str_contains($slug, 'clearance')) return \App\Models\SystemSetting::get('nin_service_enabled', 'true') === 'true';
            if ($service['category'] === 'legal') return \App\Models\SystemSetting::get('legal_service_enabled', 'true') === 'true';
            if ($service['category'] === 'commerce') return \App\Models\SystemSetting::get('auction_service_enabled', 'true') === 'true';
            if ($service['category'] === 'logistics') return \App\Models\SystemSetting::get('logistics_service_enabled', 'true') === 'true';
            if ($service['category'] === 'education') return \App\Models\SystemSetting::get('education_service_enabled', 'true') === 'true';
            if ($service['category'] === 'insurance') return \App\Models\SystemSetting::get('insurance_service_enabled', 'true') === 'true';
            return true;
        });

        $byCategory = $services->groupBy('category');

        return view('public.services.index', [
            'categories' => $categories,
            'services' => $services,
            'byCategory' => $byCategory,
        ]);
    }

    public function show(Request $request, string $slug)
    {
        $services = collect((array) config('public_services.services', []));
        $service = $services->firstWhere('slug', $slug);

        if (!$service) {
            abort(404);
        }

        return view('public.services.show', [
            'service' => $service,
            'slug' => $slug,
        ]);
    }
}

