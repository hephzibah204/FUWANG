<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSandboxServicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_sandbox_services_pages(): void
    {
        $admin = Admin::create([
            'username' => 'admin_user',
            'email' => 'admin@example.com',
            'password' => 'Admin@12345',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.sandbox.services.index'))
            ->assertOk();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.sandbox.services.show', 'plate_number'))
            ->assertOk()
            ->assertSee('sandboxForm', false);
    }

    public function test_admin_sandbox_run_validates_input(): void
    {
        $admin = Admin::create([
            'username' => 'admin_user',
            'email' => 'admin@example.com',
            'password' => 'Admin@12345',
        ]);

        $this->actingAs($admin, 'admin')
            ->postJson(route('admin.sandbox.services.run', 'plate_number'), [])
            ->assertStatus(422);
    }
}

