<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->withoutMiddleware(\App\Http\Middleware\CheckInstallation::class);
        $this->withoutMiddleware(\App\Http\Middleware\AdminSecurityMiddleware::class);
        $this->withoutMiddleware(\App\Http\Middleware\AdminAuditMiddleware::class);
        $this->withoutMiddleware(\App\Http\Middleware\CheckFeatureToggle::class);

        // Clear spatie cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        // Create permissions
        Permission::create(['name' => 'manage_roles', 'guard_name' => 'admin']);
        Permission::create(['name' => 'manage_admins', 'guard_name' => 'admin']);
        Permission::create(['name' => 'view_dashboard', 'guard_name' => 'admin']);
    }

    public function test_super_admin_can_access_roles_page()
    {
        $superAdmin = Admin::create([
            'username' => 'super',
            'email' => 'super@test.com',
            'password' => bcrypt('password'),
            'is_super_admin' => true,
        ]);

        $response = $this->actingAs($superAdmin, 'admin')->get(route('admin.roles.index'));
        $response->assertStatus(200);
    }

    public function test_sub_admin_with_permission_can_access_roles_page()
    {
        $admin = Admin::create([
            'username' => 'sub1',
            'email' => 'sub1@test.com',
            'password' => bcrypt('password'),
            'is_super_admin' => false,
        ]);
        $role = Role::create(['name' => 'role-manager', 'guard_name' => 'admin']);
        $role->givePermissionTo('manage_roles');
        $admin->assignRole($role);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.roles.index'));
        $response->assertStatus(200);
    }

    public function test_sub_admin_without_permission_cannot_access_roles_page()
    {
        $admin = Admin::create([
            'username' => 'sub2',
            'email' => 'sub2@test.com',
            'password' => bcrypt('password'),
            'is_super_admin' => false,
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.roles.index'));
        $response->assertStatus(302);
        $response->assertSessionHas('error');
    }

    public function test_sub_admin_cannot_assign_permission_they_dont_have()
    {
        $admin = Admin::create([
            'username' => 'sub3',
            'email' => 'sub3@test.com',
            'password' => bcrypt('password'),
            'is_super_admin' => false,
        ]);
        $role = Role::create(['name' => 'admin-creator', 'guard_name' => 'admin']);
        $role->givePermissionTo('manage_roles');
        $admin->assignRole($role);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.roles.store'), [
            'name' => 'new-role',
            'permissions' => ['view_dashboard'], // The creator doesn't have view_dashboard
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Privilege Escalation', session('error'));
        $this->assertDatabaseMissing('roles', ['name' => 'new-role']);
    }

    public function test_super_admin_can_assign_any_permission()
    {
        $superAdmin = Admin::create([
            'username' => 'super2',
            'email' => 'super2@test.com',
            'password' => bcrypt('password'),
            'is_super_admin' => true,
        ]);

        $response = $this->actingAs($superAdmin, 'admin')->post(route('admin.roles.store'), [
            'name' => 'new-role',
            'permissions' => ['view_dashboard'],
        ]);

        if ($response->status() === 302 && session()->has('error')) {
            dump(session('error'));
        }
        if ($response->status() === 302 && session()->has('errors')) {
            dump(session('errors')->all());
        }

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('roles', ['name' => 'new-role']);
    }
}
