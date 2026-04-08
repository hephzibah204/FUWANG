<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SystemSetting;

class AdvancedSettingsController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::all();
        return view('admin.advanced_settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = $request->input('settings');

        foreach ($settings as $key => $value) {
            SystemSetting::where('key', $key)->update(['value' => $value]);
        }

        return redirect()->route('admin.advanced_settings.index')->with('success', 'Settings updated successfully.');
    }
}
