<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserDirectoryActionsTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): Admin
    {
        return Admin::create([
            'username' => 'superadmin',
            'fullname' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'is_super_admin' => true,
        ]);
    }

    private function makeUser(string $password = 'userpass123'): User
    {
        return User::create([
            'fullname' => 'Test User',
            'username' => 'user_' . uniqid(),
            'email' => 'user_' . uniqid() . '@example.com',
            'password' => $password,
            'user_status' => 'active',
        ]);
    }

    public function test_admin_can_suspend_user_and_user_cannot_login(): void
    {
        $admin = $this->makeAdmin();
        $user = $this->makeUser('userpass123');

        $this->actingAs($admin, 'admin')
            ->postJson(route('admin.users.status', ['id' => $user->id]), ['user_status' => 'suspended'])
            ->assertOk()
            ->assertJson(['status' => true]);

        \Illuminate\Support\Facades\Auth::guard('admin')->logout();
        config(['auth.defaults.guard' => 'web']);
        \Illuminate\Support\Facades\Auth::shouldUse('web');
        $this->app['auth']->forgetGuards();
        $this->flushSession();

        $this->postJson('/login', ['email' => $user->email, 'password' => 'userpass123'])
            ->assertStatus(403)
            ->assertJson(['status' => 'error']);
    }

    public function test_admin_can_reset_user_password(): void
    {
        $admin = $this->makeAdmin();
        $user = $this->makeUser('oldpass123');

        $res = $this->actingAs($admin, 'admin')
            ->postJson(route('admin.users.reset_password', ['id' => $user->id]), []);

        $res->assertOk();
        $res->assertJson(['status' => true]);

        $temp = $res->json('temporary_password');
        $this->assertIsString($temp);
        $this->assertNotEmpty($temp);

        \Illuminate\Support\Facades\Auth::guard('admin')->logout();
        config(['auth.defaults.guard' => 'web']);
        \Illuminate\Support\Facades\Auth::shouldUse('web');
        $this->app['auth']->forgetGuards();
        $this->flushSession();

        $this->postJson('/login', ['email' => $user->email, 'password' => $temp])
            ->assertStatus(200)
            ->assertJson(['status' => 'success']);
    }
}

