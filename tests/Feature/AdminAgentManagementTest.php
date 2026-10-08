<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\EnrollmentAgent;
use App\Models\PreApprovedAgent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAgentManagementTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create(['is_super_admin' => true]);
    }

    public function test_admin_can_access_agency_command_center_overview(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.agents.overview'));

        $response->assertOk();
        $response->assertSee('Agency Network Command Center');
        $response->assertSee('Live Network Operations');
    }

    public function test_admin_can_send_broadcast_to_agents(): void
    {
        $user = User::factory()->create();
        EnrollmentAgent::create([
            'user_id' => $user->id,
            'full_name' => 'Agent Broadcast Tester',
            'phone_number' => '08012345678',
            'residential_address' => 'Sample Address',
            'office_address' => 'Sample Office',
            'bvn' => '12345678901',
            'nin' => '12345678901',
            'status' => 'approved',
            'state' => 'Lagos',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.agents.notifications.store'), [
                'subject' => 'Important Field Notice',
                'message' => 'Please update your device software before 5 PM today.',
                'target_status' => 'approved',
                'target_state' => 'Lagos',
                'send_email' => 0,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('broadcasts', [
            'subject' => 'Important Field Notice',
            'target_audience' => 'enrollment_agents',
        ]);
    }

    public function test_admin_can_manage_roster_crud_and_unclaim(): void
    {
        // 1. Create agent in roster
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.agents.roster.store'), [
                'agent_code' => 'AGT-8821',
                'full_name' => 'Emeka Okafor',
                'email' => 'emeka@example.com',
                'phone_number' => '08098765432',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pre_approved_agents', [
            'agent_code' => 'AGT-8821',
            'email' => 'emeka@example.com',
            'is_claimed' => false,
        ]);

        $rosterAgent = PreApprovedAgent::where('agent_code', 'AGT-8821')->first();

        // 2. Update roster record
        $response = $this->actingAs($this->admin, 'admin')
            ->put(route('admin.agents.roster.update', $rosterAgent->id), [
                'agent_code' => 'AGT-8821',
                'full_name' => 'Emeka C. Okafor',
                'email' => 'emeka.updated@example.com',
                'phone_number' => '08098765432',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pre_approved_agents', [
            'full_name' => 'Emeka C. Okafor',
            'email' => 'emeka.updated@example.com',
        ]);

        // 3. Mark as claimed and test 1-click unclaim
        $user = User::factory()->create();
        $rosterAgent->update([
            'is_claimed' => true,
            'claimed_at' => now(),
            'claimed_by_user_id' => $user->id,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.agents.roster.unclaim', $rosterAgent->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('pre_approved_agents', [
            'id' => $rosterAgent->id,
            'is_claimed' => false,
            'claimed_by_user_id' => null,
        ]);

        // 4. Delete roster record
        $response = $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.agents.roster.destroy', $rosterAgent->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('pre_approved_agents', [
            'id' => $rosterAgent->id,
        ]);
    }

    public function test_admin_can_edit_agent_profile_and_hardware(): void
    {
        $user = User::factory()->create();
        $agent = EnrollmentAgent::create([
            'user_id' => $user->id,
            'full_name' => 'Original Name',
            'phone_number' => '08000000000',
            'residential_address' => 'Old Address',
            'office_address' => 'Old Office',
            'bvn' => '12345678901',
            'nin' => '12345678901',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.agents.edit', $agent->id));

        $response->assertOk();
        $response->assertSee('Edit Agent Profile & Hardware', false);

        $response = $this->actingAs($this->admin, 'admin')
            ->put(route('admin.agents.update', $agent->id), [
                'full_name' => 'Updated Agent Name',
                'phone_number' => '08011112222',
                'state' => 'Abuja',
                'residential_address' => 'New Address',
                'office_address' => 'New Office Suite 4B',
                'machine_imei' => '359876543210987',
                'has_machine' => 1,
                'company_agent_code' => 'AGT-4401',
                'status' => 'approved',
                'monthly_enrollments' => 45,
                'total_enrollments' => 200,
                'is_mva_of_month' => 1,
            ]);

        $response->assertRedirect(route('admin.agents.show', $agent->id));

        $this->assertDatabaseHas('enrollment_agents', [
            'id' => $agent->id,
            'full_name' => 'Updated Agent Name',
            'phone_number' => '08011112222',
            'state' => 'Abuja',
            'machine_imei' => '359876543210987',
            'has_machine' => true,
            'company_agent_code' => 'AGT-4401',
            'status' => 'approved',
            'monthly_enrollments' => 45,
            'total_enrollments' => 200,
            'is_mva_of_month' => true,
        ]);
    }

    public function test_admin_can_delete_enrollment_agent_profile_without_deleting_user(): void
    {
        $user = User::factory()->create();
        $agent = EnrollmentAgent::create([
            'user_id' => $user->id,
            'full_name' => 'Agent Profile To Delete',
            'phone_number' => '08099998888',
            'residential_address' => 'Test Address',
            'office_address' => 'Test Office',
            'bvn' => '11111111111',
            'nin' => '22222222222',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.agents.destroy', $agent->id));

        $response->assertRedirect(route('admin.agents.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('enrollment_agents', ['id' => $agent->id]);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_admin_can_update_agent_enrollment_counts(): void
    {
        $user = User::factory()->create();
        $agent = EnrollmentAgent::create([
            'user_id' => $user->id,
            'full_name' => 'Enrollment Metrics Agent',
            'phone_number' => '08012345678',
            'residential_address' => '123 Test St',
            'office_address' => 'Office 10',
            'bvn' => '12345678901',
            'nin' => '98765432109',
            'status' => 'approved',
            'total_enrollments' => 10,
            'monthly_enrollments' => 5,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.agents.update_enrollments', $agent->id), [
                'total_enrollments' => 1250,
                'monthly_enrollments' => 340,
            ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('enrollment_agents', [
            'id' => $agent->id,
            'total_enrollments' => 1250,
            'monthly_enrollments' => 340,
        ]);
    }

    public function test_admin_can_bulk_approve_and_bulk_suspend_agents(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $agent1 = EnrollmentAgent::create([
            'user_id' => $user1->id,
            'full_name' => 'Agent One',
            'phone_number' => '08011111111',
            'residential_address' => '123 Test St',
            'office_address' => 'Office 1',
            'bvn' => '12345678901',
            'nin' => '98765432101',
            'status' => 'pending',
        ]);

        $agent2 = EnrollmentAgent::create([
            'user_id' => $user2->id,
            'full_name' => 'Agent Two',
            'phone_number' => '08022222222',
            'residential_address' => '456 Test St',
            'office_address' => 'Office 2',
            'bvn' => '12345678902',
            'nin' => '98765432102',
            'status' => 'pending',
        ]);

        // Bulk approve
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.agents.bulk_action'), [
                'action' => 'approve',
                'agent_ids' => [$agent1->id, $agent2->id],
            ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('enrollment_agents', ['id' => $agent1->id, 'status' => 'approved']);
        $this->assertDatabaseHas('enrollment_agents', ['id' => $agent2->id, 'status' => 'approved']);

        // Bulk suspend
        $response2 = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.agents.bulk_action'), [
                'action' => 'suspend',
                'agent_ids' => [$agent1->id, $agent2->id],
            ]);

        $response2->assertSessionHas('success');
        $this->assertDatabaseHas('enrollment_agents', ['id' => $agent1->id, 'status' => 'suspended']);
        $this->assertDatabaseHas('enrollment_agents', ['id' => $agent2->id, 'status' => 'suspended']);
    }

    public function test_admin_can_send_direct_notification_to_agent(): void
    {
        $user = User::factory()->create();
        $agent = EnrollmentAgent::create([
            'user_id' => $user->id,
            'full_name' => 'Alert Agent',
            'phone_number' => '08033333333',
            'residential_address' => '789 Alert St',
            'office_address' => 'Office 3',
            'bvn' => '12345678903',
            'nin' => '98765432103',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.agents.notify_direct', $agent->id), [
                'subject' => 'Terminal Configuration Update',
                'message' => 'Please update your NIMC enrollment app to build 2.4.',
                'send_email' => 0,
            ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('broadcasts', [
            'subject' => 'Terminal Configuration Update',
            'target_audience' => 'enrollment_agents',
        ]);
    }
}
