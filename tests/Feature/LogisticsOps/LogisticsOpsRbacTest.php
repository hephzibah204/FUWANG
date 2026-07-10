<?php

namespace Tests\Feature\LogisticsOps;

use App\Models\Admin;
use App\Models\LogisticsRequest;
use App\Models\LogisticsStaff;
use App\Models\User;
use Database\Seeders\LogisticsRbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogisticsOpsRbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LogisticsRbacSeeder::class);
    }

    public function test_super_admin_can_create_logistics_manager_account(): void
    {
        $admin = Admin::factory()->createOne(['is_super_admin' => true]);
        assert($admin instanceof Admin);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.logistics-staff.store'), [
            'fullname' => 'Logistics Manager',
            'email' => 'manager@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'logistics_manager',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.logistics-staff.index'));

        $staff = LogisticsStaff::query()->where('email', 'manager@example.com')->first();
        $this->assertNotNull($staff);
        $this->assertTrue($staff->hasRole('logistics_manager'));
        $this->assertTrue($staff->is_active);
    }

    public function test_logistics_manager_can_access_ops_and_create_orders(): void
    {
        $staff = LogisticsStaff::factory()->createOne(['email' => 'm1@example.com']);
        assert($staff instanceof LogisticsStaff);
        $staff->assignRole('logistics_manager');

        $this->actingAs($staff, 'logistics_staff')
            ->get('/logistics/ops/dashboard')
            ->assertOk();

        $this->actingAs($staff, 'logistics_staff')
            ->get('/logistics/ops/orders/create')
            ->assertOk();

        $user = User::factory()->createOne();
        assert($user instanceof User);
        $response = $this->actingAs($staff, 'logistics_staff')->post('/logistics/ops/orders', [
            'user_id' => $user->id,
            'sender_name' => 'Sender',
            'sender_address' => 'Sender address',
            'recipient_name' => 'Recipient',
            'recipient_address' => 'Recipient address',
            'delivery_type' => 'standard',
            'amount' => 1000,
        ]);

        $response->assertRedirect('/logistics/ops/orders');
        $this->assertDatabaseCount('logistics_requests', 1);
    }

    public function test_logistics_officer_is_scoped_to_assigned_orders_and_cannot_create_orders(): void
    {
        $officer = LogisticsStaff::factory()->createOne(['email' => 'o1@example.com']);
        assert($officer instanceof LogisticsStaff);
        $officer->assignRole('logistics_officer');

        $otherOfficer = LogisticsStaff::factory()->createOne(['email' => 'o2@example.com']);
        assert($otherOfficer instanceof LogisticsStaff);
        $otherOfficer->assignRole('logistics_officer');

        $order1 = LogisticsRequest::factory()->create(['assigned_officer_id' => $officer->id]);
        $order2 = LogisticsRequest::factory()->create(['assigned_officer_id' => $otherOfficer->id]);

        $index = $this->actingAs($officer, 'logistics_staff')->get('/logistics/ops/orders');
        $index->assertOk();
        $index->assertSee($order1->tracking_id);
        $index->assertDontSee($order2->tracking_id);

        $this->actingAs($officer, 'logistics_staff')
            ->get('/logistics/ops/orders/create')
            ->assertStatus(403);
    }

    public function test_jwt_login_and_orders_api_access(): void
    {
        $staff = LogisticsStaff::factory()->createOne(['email' => 'jwt.manager@example.com', 'password' => 'Password123!']);
        assert($staff instanceof LogisticsStaff);
        $staff->assignRole('logistics_manager');

        $login = $this->postJson('/api/v1/logistics/ops/auth/login', [
            'email' => 'jwt.manager@example.com',
            'password' => 'Password123!',
        ]);

        $login->assertOk()->assertJsonStructure(['status', 'token', 'expires_at', 'role']);
        $token = $login->json('token');
        $this->assertNotEmpty($token);

        LogisticsRequest::factory()->count(2)->create();

        $resp = $this->getJson('/api/v1/logistics/ops/orders', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $resp->assertOk()->assertJsonPath('status', 'success');
    }
}
