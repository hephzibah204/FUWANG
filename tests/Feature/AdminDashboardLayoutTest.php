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

    public function test_admin_visiting_homepage_renders_clean_public_layout(): void
    {
        $admin = Admin::create([
            'username' => 'admin_user_2',
            'email' => 'admin2@example.com',
            'password' => 'Admin@12345',
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('home'));

        $response->assertOk();
        // Public navbar should be visible with Admin Dashboard button
        $response->assertSee('public-nav', false);
        $response->assertSee('Admin Dashboard', false);
        // Dashboard sidebar and top-header must NOT be visible
        $response->assertDontSee('id="sidebar"', false);
        $response->assertDontSee('class="top-header"', false);
        $response->assertDontSee('class="dashboard-content', false);
    }

    public function test_admin_visiting_blog_renders_clean_public_layout(): void
    {
        $admin = Admin::create([
            'username' => 'admin_user_3',
            'email' => 'admin3@example.com',
            'password' => 'Admin@12345',
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('blog.index'));

        $response->assertOk();
        $response->assertSee('public-nav', false);
        $response->assertDontSee('id="sidebar"', false);
        $response->assertDontSee('class="top-header"', false);
    }

    public function test_web_user_visiting_homepage_renders_clean_public_layout(): void
    {
        $user = \App\Models\User::create([
            'fullname' => 'Test User',
            'username' => 'testuser',
            'email' => 'testuser@example.com',
            'number' => '08012345678',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($user, 'web')->get(route('home'));

        $response->assertOk();
        $response->assertSee('public-nav', false);
        $response->assertSee('Dashboard', false);
        $response->assertDontSee('id="sidebar"', false);
        $response->assertDontSee('class="top-header"', false);
    }

    public function test_guest_visiting_homepage_renders_clean_public_layout(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('public-nav', false);
        $response->assertSee('Login', false);
        $response->assertSee('Get Started', false);
        $response->assertDontSee('id="sidebar"', false);
        $response->assertDontSee('class="top-header"', false);
    }
}
