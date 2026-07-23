<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPagesPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function makeAdmin(array $perms = [], bool $super = false): Admin
    {
        $admin = Admin::create([
            'username' => 'admin_' . uniqid(),
            'email' => 'admin_' . uniqid() . '@example.com',
            'password' => 'password1234',
            'is_super_admin' => $super,
        ]);

        foreach ($perms as $permName) {
            $permission = \Spatie\Permission\Models\Permission::findOrCreate($permName, 'admin');
            $admin->givePermissionTo($permission);
        }

        return $admin;
    }

    public function test_admin_without_permission_cannot_access_pages(): void
    {
        $admin = $this->makeAdmin([]);
        $this->actingAs($admin, 'admin');

        $resp = $this->get(route('admin.pages.index'));
        $resp->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_with_permission_can_access_pages(): void
    {
        $admin = $this->makeAdmin(['manage_pages']);
        $this->actingAs($admin, 'admin');

        $resp = $this->get(route('admin.pages.index'));
        $resp->assertStatus(200);
    }

    public function test_public_page_show_renders(): void
    {
        Page::create([
            'title' => 'About Us',
            'slug' => 'about-us',
            'content' => 'About content',
            'status' => 'published',
        ]);
        $resp = $this->get(route('pages.show', 'about-us'));
        $resp->assertStatus(200);
        $resp->assertSee('About Us');
    }
}
