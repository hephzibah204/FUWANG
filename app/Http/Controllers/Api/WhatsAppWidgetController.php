<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SystemSetting;
use App\Models\WhatsAppClickLog;
use Illuminate\Support\Facades\Cache;

class WhatsAppWidgetController extends Controller
{
    /**
     * Retrieve the WhatsApp widget configuration.
     * Caches the result briefly to reduce DB load, but short enough to feel "real-time".
     */
    public function config()
    {
        $settings = Cache::remember('whatsapp_widget_config', 30, function () {
            return [
                'enabled' => SystemSetting::get('whatsapp_enabled', '1') === '1',
                'number' => SystemSetting::get('whatsapp_number', ''),
                'position' => SystemSetting::get('whatsapp_position', 'bottom-right'),
                'x_offset' => (int) SystemSetting::get('whatsapp_x_offset', '30'),
                'y_offset' => (int) SystemSetting::get('whatsapp_y_offset', '30'),
                'size' => (int) SystemSetting::get('whatsapp_size', '60'),
                'color' => SystemSetting::get('whatsapp_color', '#25d366'),
                'hover_color' => SystemSetting::get('whatsapp_hover_color', '#128c7e'),
                'animation' => SystemSetting::get('whatsapp_animation', 'bounce'),
                'display_pages' => SystemSetting::get('whatsapp_display_pages', 'all'),
                'hours_start' => SystemSetting::get('whatsapp_operating_hours_start', '00:00'),
                'hours_end' => SystemSetting::get('whatsapp_operating_hours_end', '23:59'),
                'timezone' => SystemSetting::get('whatsapp_timezone', 'Africa/Lagos'),
                'prefilled_message' => SystemSetting::get('whatsapp_prefilled_message', 'Hello, I need help with...'),
            ];
        });

        // Add current server time to help frontend calculate timezone differences
        $settings['server_time'] = now()->timezone($settings['timezone'])->format('H:i');

        return response()->json([
            'status' => true,
            'data' => $settings
        ]);
    }

    /**
     * Track a click on the WhatsApp widget.
     * Uses rate limiting in the route definition to prevent abuse.
     */
    public function trackClick(Request $request)
    {
        $request->validate([
            'page_url' => 'nullable|url|max:255'
        ]);

        WhatsAppClickLog::create([
            'user_id' => auth('sanctum')->id() ?? auth()->id(),
            'page_url' => $request->page_url,
            'ip_address' => $request->ip(),
            'user_agent' => substr($request->userAgent(), 0, 255),
        ]);

        return response()->json(['status' => true]);
    }
}
