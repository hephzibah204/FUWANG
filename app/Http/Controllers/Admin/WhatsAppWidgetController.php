<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SystemSetting;
use App\Models\AdminAuditLog;

class WhatsAppWidgetController extends Controller
{
    public function index()
    {
        $settings = [
            'whatsapp_enabled' => SystemSetting::get('whatsapp_enabled', '1'),
            'whatsapp_number' => SystemSetting::get('whatsapp_number', ''),
            'whatsapp_position' => SystemSetting::get('whatsapp_position', 'bottom-right'),
            'whatsapp_x_offset' => SystemSetting::get('whatsapp_x_offset', '30'),
            'whatsapp_y_offset' => SystemSetting::get('whatsapp_y_offset', '30'),
            'whatsapp_size' => SystemSetting::get('whatsapp_size', '60'),
            'whatsapp_color' => SystemSetting::get('whatsapp_color', '#25d366'),
            'whatsapp_hover_color' => SystemSetting::get('whatsapp_hover_color', '#128c7e'),
            'whatsapp_animation' => SystemSetting::get('whatsapp_animation', 'bounce'),
            'whatsapp_display_pages' => SystemSetting::get('whatsapp_display_pages', 'all'), // all, auth, guest
            'whatsapp_operating_hours_start' => SystemSetting::get('whatsapp_operating_hours_start', '00:00'),
            'whatsapp_operating_hours_end' => SystemSetting::get('whatsapp_operating_hours_end', '23:59'),
            'whatsapp_timezone' => SystemSetting::get('whatsapp_timezone', 'Africa/Lagos'),
            'whatsapp_prefilled_message' => SystemSetting::get('whatsapp_prefilled_message', 'Hello, I need help with...'),
        ];

        return view('admin.whatsapp_widget.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'whatsapp_enabled' => 'required|in:0,1',
            'whatsapp_number' => 'nullable|string|max:20',
            'whatsapp_position' => 'required|in:bottom-right,bottom-left,top-right,top-left',
            'whatsapp_x_offset' => 'required|numeric|min:0|max:500',
            'whatsapp_y_offset' => 'required|numeric|min:0|max:500',
            'whatsapp_size' => 'required|numeric|min:30|max:100',
            'whatsapp_color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/i',
            'whatsapp_hover_color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/i',
            'whatsapp_animation' => 'required|in:none,bounce,pulse,fade',
            'whatsapp_display_pages' => 'required|in:all,auth,guest',
            'whatsapp_operating_hours_start' => 'required|date_format:H:i',
            'whatsapp_operating_hours_end' => 'required|date_format:H:i',
            'whatsapp_timezone' => 'required|timezone',
            'whatsapp_prefilled_message' => 'nullable|string|max:255',
        ]);

        $changes = [];

        foreach ($validated as $key => $value) {
            $oldValue = SystemSetting::get($key);
            if ($oldValue != $value) {
                $changes[$key] = ['old' => $oldValue, 'new' => $value];
                SystemSetting::put($key, $value, 'whatsapp_widget');
            }
        }

        if (!empty($changes)) {
            // Log the changes
            if (class_exists(AdminAuditLog::class)) {
                AdminAuditLog::create([
                    'admin_id' => auth('admin')->id(),
                    'action' => 'Updated WhatsApp Widget Settings',
                    'details' => json_encode($changes),
                    'ip_address' => $request->ip(),
                ]);
            }
        }

        return redirect()->back()->with('success', 'WhatsApp Widget configuration updated successfully.');
    }
}
