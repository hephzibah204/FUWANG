<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\AdminAuditLog;
use App\Models\Funding;
use App\Models\User;
use App\Models\AccountBalance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SelfFundingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Set up necessary data
    }

    public function test_only_super_admin_can_access_self_funding_page()
    {
        $admin = Admin::factory()->create(['is_super_admin' => false]);
        $this->actingAs($admin, 'admin');

        $response = $this->get(route('admin.self_funding.index'));
        $response->assertStatus(302); // Redirects to dashboard with error
    }

    public function test_super_admin_can_access_self_funding_page()
    {
        $admin = Admin::factory()->create(['is_super_admin' => true]);
        $this->actingAs($admin, 'admin');

        $response = $this->get(route('admin.self_funding.index'));
        $response->assertStatus(200);
    }

    public function test_super_admin_can_fund_their_own_account()
    {
        $email = 'superadmin@example.com';
        $admin = Admin::factory()->create([
            'email' => $email,
            'is_super_admin' => true
        ]);
        
        $this->actingAs($admin, 'admin');

        $response = $this->postJson(route('admin.self_funding.fund'), ['amount' => 5000]);

        $response->assertStatus(200);
        $response->assertJson(['status' => true]);

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertEquals(5000, $user->balance->user_balance);
    }

    public function test_funding_page_auto_creates_user_if_missing()
    {
        $email = 'newadmin@example.com';
        $admin = Admin::factory()->create([
            'email' => $email,
            'is_super_admin' => true
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->get(route('admin.self_funding.index'));
        $response->assertStatus(200);

        $this->assertDatabaseHas('users', ['email' => $email]);
    }

    public function test_funding_limit_is_enforced()
    {
        $email = 'superadmin@example.com';
        $admin = Admin::factory()->create([
            'email' => $email,
            'is_super_admin' => true
        ]);
        
        $user = User::factory()->create(['email' => $email]);

        $this->actingAs($admin, 'admin');

        // Assuming default limit is 10M
        $response = $this->postJson(route('admin.self_funding.fund'), [
            'amount' => 10000001.00
        ]);

        $response->assertStatus(422);
    }
}
