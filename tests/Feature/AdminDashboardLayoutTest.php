<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_renders_authenticated_layout(): void
    {
        $admin = Admin::create([
            'username' => 'admin_user',
            'email' => 'admin@example.com',
            'password' => 'Admin@12345',
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('assets/nexus/css/nexus.css', false);
        $response->assertSee('sidebar', false);
        $response->assertSee('main-content', false);
        $response->assertSee('dashboard-content', false);
    }
}
