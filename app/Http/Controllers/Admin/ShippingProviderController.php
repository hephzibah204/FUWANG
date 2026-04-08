<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShippingProviderController extends Controller
{
    public function index()
    {
        $providers = ShippingProvider::all();
        return view('admin.shipping_providers.index', compact('providers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        ShippingProvider::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'is_active' => false,
        ]);

        return back()->with('success', 'Shipping provider added successfully.');
    }

    public function update(Request $request, ShippingProvider $provider)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'api_key' => 'nullable|string',
            'api_secret' => 'nullable|string',
            'api_base_url' => 'nullable|url',
            'is_active' => 'required|boolean',
        ]);

        $provider->update($request->all());

        return back()->with('success', 'Shipping provider updated successfully.');
    }

    public function toggle(ShippingProvider $provider)
    {
        $provider->update(['is_active' => !$provider->is_active]);
        return back()->with('success', 'Provider status toggled.');
    }
}
