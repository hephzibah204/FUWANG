<?php

namespace Tests\Feature;

use App\Models\Broadcast;
use App\Models\EnrollmentAgent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentAgentSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_agent_enrollment_application(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'user_status' => 'active',
        ]);

        $response = $this->actingAs($user)->post(route('agent.register.submit'), [
            'agent_type' => 'existing',
            'company_agent_code' => 'FUWA-AGT-8842',
            'full_name' => 'John Doe Agent',
            'email' => $user->email,
            'phone_number' => '08012345678',
            'state' => 'Lagos',
            'residential_address' => '123 Main Street, Ikeja',
            'office_address' => '456 Commercial Avenue, Ikeja',
            'bvn' => '12345678901',
            'nin' => '10987654321',
            'has_machine' => '1',
            'machine_imei' => '864201041234567',
        ]);

        $response->assertRedirect(route('agent.onboarding.index'));
        $this->assertDatabaseHas('enrollment_agents', [
            'user_id' => $user->id,
            'agent_type' => 'existing',
            'company_agent_code' => 'FUWA-AGT-8842',
            'is_fast_tracked' => true,
            'full_name' => 'John Doe Agent',
            'bvn' => '12345678901',
            'nin' => '10987654321',
            'machine_imei' => '864201041234567',
            'status' => 'pending',
            'nin_verified' => false,
        ]);
    }

    public function test_approved_agent_default_login_redirection_and_mode_switching(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'user_status' => 'active',
            'password' => bcrypt('password123'),
        ]);

        $agent = EnrollmentAgent::create([
            'user_id' => $user->id,
            'full_name' => 'John Doe Agent',
            'phone_number' => '08012345678',
            'residential_address' => '123 Main Street',
            'office_address' => '456 Office Street',
            'bvn' => '12345678901',
            'nin' => '10987654321',
            'machine_imei' => '864201041234567',
            'picture_path' => 'agent_kyc/test_agent.jpg',
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        // Login as approved agent
        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('agent.dashboard'));
        $this->assertEquals('agency', session('active_dashboard_mode'));

        // Switch mode to user
        $switchRes = $this->actingAs($user)->post(route('agent.switch_mode'), [
            'mode' => 'user',
        ]);

        $switchRes->assertRedirect(route('dashboard'));
        $this->assertEquals('user', session('active_dashboard_mode'));
    }

    public function test_admin_can_approve_reject_and_publish_leaderboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'user_status' => 'active',
        ]);

        $user = User::factory()->create();
        $agent = EnrollmentAgent::create([
            'user_id' => $user->id,
            'full_name' => 'Jane Agent',
            'phone_number' => '08099999999',
            'residential_address' => 'Address 1',
            'office_address' => 'Address 2',
            'bvn' => '11111111111',
            'nin' => '22222222222',
            'machine_imei' => '888888888888888',
            'status' => 'pending',
        ]);

        // Approve agent
        $approveRes = $this->actingAs($admin, 'admin')->post(route('admin.agents.approve', $agent->id));
        $approveRes->assertRedirect();
        $this->assertDatabaseHas('enrollment_agents', [
            'id' => $agent->id,
            'status' => 'approved',
        ]);

        // Publish MVA
        $publishRes = $this->actingAs($admin, 'admin')->post(route('admin.agents.leaderboard.publish'), [
            'mva_agent_id' => $agent->id,
        ]);
        $publishRes->assertRedirect();
        $this->assertDatabaseHas('enrollment_agents', [
            'id' => $agent->id,
            'is_mva_of_month' => true,
        ]);
    }

    public function test_broadcast_supports_targeting_enrollment_agents(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $broadcast = Broadcast::create([
            'subject' => 'Special Announcement for Enrollment Agents',
            'message' => 'Please calibrate your biometric enrollment machines.',
            'target_audience' => 'enrollment_agents',
            'status' => 'draft',
            'created_by' => $admin->id,
        ]);

        $this->assertEquals('enrollment_agents', $broadcast->target_audience);
    }

    public function test_claimed_preapproved_profile_cannot_be_reclaimed_by_another_user(): void
    {
        $preApproved = \App\Models\PreApprovedAgent::create([
            'agent_code' => 'FUWA-CLAIM-001',
            'full_name' => 'Original PreApproved Agent',
            'email' => 'preapproved@example.com',
            'phone_number' => '08099887766',
            'is_claimed' => true,
            'claimed_at' => now(),
        ]);

        $user = User::factory()->create([
            'email_verified_at' => now(),
            'user_status' => 'active',
        ]);

        $response = $this->actingAs($user)->from(route('agent.register'))->post(route('agent.register.submit'), [
            'agent_type' => 'existing',
            'company_agent_code' => 'FUWA-CLAIM-001',
            'claim_otp' => '123456',
            'full_name' => 'Impostor Agent',
            'email' => 'impostor@example.com',
            'phone_number' => '08011223344',
            'state' => 'Lagos',
            'residential_address' => '123 Address',
            'office_address' => '456 Address',
            'bvn' => '12345678901',
            'nin' => '10987654321',
            'has_machine' => '1',
            'machine_imei' => '864201041234567',
        ]);

        $response->assertRedirect(route('agent.register'));
        $response->assertSessionHasErrors(['company_agent_code']);
    }

    public function test_existing_agent_requires_machine_imei(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'user_status' => 'active',
        ]);

        $response = $this->actingAs($user)->from(route('agent.register'))->post(route('agent.register.submit'), [
            'agent_type' => 'existing',
            'company_agent_code' => 'FUWA-EXIST-002',
            'full_name' => 'Existing Agent No IMEI',
            'email' => $user->email,
            'phone_number' => '08012345678',
            'state' => 'Lagos',
            'residential_address' => 'Address',
            'office_address' => 'Address',
            'bvn' => '12345678901',
            'nin' => '10987654321',
            'has_machine' => '0',
            'machine_imei' => '',
        ]);

        $response->assertRedirect(route('agent.register'));
        $response->assertSessionHasErrors(['machine_imei']);
    }

    public function test_existing_agent_can_claim_preapproved_profile_without_otp(): void
    {
        $preApproved = \App\Models\PreApprovedAgent::create([
            'agent_code' => 'FUWA-NOOTP-001',
            'full_name' => 'Direct Claim Agent',
            'email' => 'nootpagent@example.com',
            'phone_number' => '08077665544',
            'is_claimed' => false,
        ]);

        $user = User::factory()->create([
            'email_verified_at' => now(),
            'user_status' => 'active',
        ]);

        $response = $this->actingAs($user)->post(route('agent.register.submit'), [
            'agent_type' => 'existing',
            'company_agent_code' => 'FUWA-NOOTP-001',
            'full_name' => 'Direct Claim Agent',
            'email' => 'nootpagent@example.com',
            'phone_number' => '08077665544',
            'state' => 'Lagos',
            'residential_address' => 'Address',
            'office_address' => 'Address',
            'bvn' => '12345678901',
            'nin' => '10987654321',
            'has_machine' => '1',
            'machine_imei' => '864201041234567',
        ]);

        $response->assertRedirect(route('agent.onboarding.index'));
        $this->assertDatabaseHas('enrollment_agents', [
            'user_id' => $user->id,
            'company_agent_code' => 'FUWA-NOOTP-001',
            'is_fast_tracked' => true,
        ]);
    }
}
