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
}
