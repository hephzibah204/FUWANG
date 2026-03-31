<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\SystemSetting;
use App\Models\WhatsAppClickLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_whatsapp_settings()
    {
        $admin = Admin::first() ?? Admin::create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'username' => 'superadmin',
            'email' => 'admin_test@nexus.com',
            'password' => bcrypt('password123'),
            'role' => 'super_admin',
            'status' => 'active'
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.settings.whatsapp_widget.update'), [
            'whatsapp_enabled' => '1',
            'whatsapp_number' => '2348012345678',
            'whatsapp_position' => 'bottom-right',
            'whatsapp_x_offset' => 40,
            'whatsapp_y_offset' => 40,
            'whatsapp_size' => 70,
            'whatsapp_color' => '#00ff00',
            'whatsapp_hover_color' => '#00cc00',
            'whatsapp_animation' => 'bounce',
            'whatsapp_display_pages' => 'all',
            'whatsapp_operating_hours_start' => '08:00',
            'whatsapp_operating_hours_end' => '17:00',
            'whatsapp_timezone' => 'Africa/Lagos',
            'whatsapp_prefilled_message' => 'Help me!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('2348012345678', SystemSetting::get('whatsapp_number'));
        $this->assertEquals('70', SystemSetting::get('whatsapp_size'));
    }

    public function test_api_returns_correct_widget_config()
    {
        // Mock installation check or skip middleware if needed
        $this->withoutMiddleware(\App\Http\Middleware\CheckInstallation::class);

        SystemSetting::put('whatsapp_enabled', '1', 'whatsapp_widget');
        SystemSetting::put('whatsapp_number', '12345', 'whatsapp_widget');
        SystemSetting::put('whatsapp_color', '#123456', 'whatsapp_widget');

        $response = $this->getJson('/api/whatsapp-widget/config');
        
        $response->assertStatus(200)
                 ->assertJsonPath('data.enabled', true)
                 ->assertJsonPath('data.number', '12345')
                 ->assertJsonPath('data.color', '#123456');
    }

    public function test_widget_click_logging()
    {
        $this->withoutMiddleware(\App\Http\Middleware\CheckInstallation::class);

        $response = $this->postJson('/api/whatsapp-widget/click', [
            'page_url' => 'https://example.com/test'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('whatsapp_click_logs', [
            'page_url' => 'https://example.com/test'
        ]);
    }
}
