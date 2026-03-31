<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeatureToggle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FeatureToggleController extends Controller
{
    /**
     * Display a listing of feature toggles.
     */
    public function __construct()
    {
        $this->middleware('super_admin');
    }

    public function index()
    {
        $features = FeatureToggle::orderBy('feature_name')->get();
        return view('admin.features.index', compact('features'));
    }

    /**
     * Store a newly created feature toggle.
     */
    public function store(Request $request)
    {
        $request->validate([
            'feature_name'    => 'required|string|unique:feature_toggles,feature_name|max:255',
            'is_active'       => 'boolean',
            'offline_message' => 'nullable|string|max:255',
        ]);

        FeatureToggle::create([
            'feature_name'    => strtolower((string)$request->feature_name),
            'is_active'       => $request->has('is_active'),
            'offline_message' => $request->offline_message,
        ]);

        Cache::forget('feature_toggle:' . strtolower((string) $request->feature_name));

        return back()->with('success', 'Feature toggle added successfully.');
    }

    /**
     * Update the specified feature toggle.
     */
    public function update(Request $request, $id)
    {
        $feature = FeatureToggle::findOrFail($id);
        
        $request->validate([
            'offline_message' => 'nullable|string|max:255',
        ]);

        $feature->update([
            'is_active'       => $request->has('is_active'),
            'offline_message' => $request->offline_message,
        ]);

        Cache::forget('feature_toggle:' . strtolower((string) $feature->feature_name));

        return back()->with('success', 'Feature toggle updated successfully.');
    }

    /**
     * Remove the specified feature toggle.
     */
    public function destroy($id)
    {
        $feature = FeatureToggle::findOrFail($id);
        $featureName = strtolower((string) $feature->feature_name);
        $feature->delete();

        Cache::forget('feature_toggle:' . $featureName);

        return back()->with('success', 'Feature toggle removed.');
    }
}
