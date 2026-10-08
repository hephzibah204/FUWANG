<?php

namespace Tests\Feature;

use App\Models\Admin;
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

        $response->assertRedirect(route('agent.dashboard'));
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

        // Publish MVA / Sync
        $publishRes = $this->actingAs($admin, 'admin')->post(route('admin.agents.leaderboard.publish'), [
            'mva_agent_id' => $agent->id,
        ]);
        $publishRes->assertRedirect();
        $this->assertDatabaseHas('enrollment_agents', [
            'id' => $agent->id,
            'is_mva_of_month' => true,
        ]);
    }

    public function test_leaderboard_and_mvp_are_automatically_calculated_by_total_numbers_of_enrollment_per_agent_recorded_by_admin(): void
    {
        $admin = Admin::factory()->create(['is_super_admin' => true]);

        $userA = User::factory()->create();
        $agentA = EnrollmentAgent::create([
            'user_id' => $userA->id,
            'full_name' => 'Agent Alpha',
            'phone_number' => '08011111111',
            'residential_address' => 'Address A',
            'office_address' => 'Office A',
            'bvn' => '11111111111',
            'nin' => '22222222222',
            'machine_imei' => '111122223333444',
            'status' => 'approved',
            'total_enrollments' => 50,
            'monthly_enrollments' => 10,
        ]);

        $userB = User::factory()->create();
        $agentB = EnrollmentAgent::create([
            'user_id' => $userB->id,
            'full_name' => 'Agent Beta',
            'phone_number' => '08022222222',
            'residential_address' => 'Address B',
            'office_address' => 'Office B',
            'bvn' => '33333333333',
            'nin' => '44444444444',
            'machine_imei' => '555566667777888',
            'status' => 'approved',
            'total_enrollments' => 20,
            'monthly_enrollments' => 20,
        ]);

        // When admin records enrollments for Agent B to 300 total enrollments
        $resUpdateB = $this->actingAs($admin, 'admin')->post(route('admin.agents.update_enrollments', $agentB->id), [
            'total_enrollments' => 300,
            'monthly_enrollments' => 50,
        ]);
        $resUpdateB->assertRedirect();

        // Agent B must automatically become the MVP because 300 > 50
        $this->assertTrue($agentB->fresh()->is_mva_of_month);
        $this->assertFalse($agentA->fresh()->is_mva_of_month);

        // Leaderboard page ranks Agent B first, Agent A second
        $leaderboardRes = $this->actingAs($admin, 'admin')->get(route('admin.agents.leaderboard'));
        $leaderboardRes->assertStatus(200);
        $leaderboardRes->assertSeeInOrder(['Agent Beta', 'Agent Alpha']);
        $leaderboardRes->assertSee('MVP (Rank #1)');

        // Now admin records Agent A to 500 total enrollments
        $resUpdateA = $this->actingAs($admin, 'admin')->post(route('admin.agents.update_enrollments', $agentA->id), [
            'total_enrollments' => 500,
            'monthly_enrollments' => 80,
        ]);
        $resUpdateA->assertRedirect();

        // Agent A must now automatically be crowned MVP because 500 > 300
        $this->assertTrue($agentA->fresh()->is_mva_of_month);
        $this->assertFalse($agentB->fresh()->is_mva_of_month);

        // Agent A visits dashboard and sees their MVP status and rank #1
        $dashboardRes = $this->actingAs($userA)->get(route('agent.dashboard'));
        $dashboardRes->assertStatus(200);
        $dashboardRes->assertSee('MOST VALUABLE AGENT (MVP)');
        $dashboardRes->assertSee('500');
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

    public function test_existing_agent_profile_claim_requires_valid_email_otp(): void
    {
        $preApproved = \App\Models\PreApprovedAgent::create([
            'agent_code' => 'FUWA-OTP-001',
            'full_name' => 'OTP Claim Agent',
            'email' => 'otpagent@example.com',
            'phone_number' => '08077665544',
            'is_claimed' => false,
        ]);

        $user = User::factory()->create([
            'email_verified_at' => now(),
            'user_status' => 'active',
        ]);

        // Attempt without OTP -> should fail
        $response = $this->actingAs($user)->from(route('agent.register'))->post(route('agent.register.submit'), [
            'agent_type' => 'existing',
            'company_agent_code' => 'FUWA-OTP-001',
            'full_name' => 'OTP Claim Agent',
            'email' => 'otpagent@example.com',
            'phone_number' => '08077665544',
            'state' => 'Lagos',
            'residential_address' => 'Address',
            'office_address' => 'Address',
            'bvn' => '12345678901',
            'nin' => '10987654321',
            'has_machine' => '1',
            'machine_imei' => '864201041234567',
        ]);

        $response->assertRedirect(route('agent.register'));
        $response->assertSessionHasErrors(['claim_otp']);

        // Now submit with valid OTP in session
        $responseSuccess = $this->actingAs($user)->withSession([
            'claim_otp_code_FUWA-OTP-001' => '654321',
            'claim_otp_expires_FUWA-OTP-001' => now()->addMinutes(15),
        ])->post(route('agent.register.submit'), [
            'agent_type' => 'existing',
            'company_agent_code' => 'FUWA-OTP-001',
            'claim_otp' => '654321',
            'full_name' => 'OTP Claim Agent',
            'email' => 'otpagent@example.com',
            'phone_number' => '08077665544',
            'state' => 'Lagos',
            'residential_address' => 'Address',
            'office_address' => 'Address',
            'bvn' => '12345678901',
            'nin' => '10987654321',
            'has_machine' => '1',
            'machine_imei' => '864201041234567',
        ]);

        $responseSuccess->assertRedirect(route('agent.dashboard'));
        $this->assertDatabaseHas('enrollment_agents', [
            'user_id' => $user->id,
            'company_agent_code' => 'FUWA-OTP-001',
            'is_fast_tracked' => true,
        ]);
    }

    public function test_send_claim_otp_endpoint_dispatches_email_and_stores_session(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $preApproved = \App\Models\PreApprovedAgent::create([
            'agent_code' => 'FUWA-SEND-999',
            'full_name' => 'Send OTP Agent',
            'email' => 'sendotp@example.com',
            'phone_number' => '08011223344',
            'is_claimed' => false,
        ]);

        $response = $this->postJson(route('agent.send_claim_otp'), [
            'company_agent_code' => 'fuwa-send-999', // test lowercase handling
        ]);

        $response->assertOk();
        $response->assertJson([
            'ok' => true,
        ]);

        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\AgentClaimVerificationMail::class, function ($mail) {
            return $mail->hasTo('sendotp@example.com');
        });

        $this->assertNotNull(session('claim_otp_code_FUWA-SEND-999'));
    }

    public function test_expired_claim_otp_fails_verification(): void
    {
        $preApproved = \App\Models\PreApprovedAgent::create([
            'agent_code' => 'FUWA-EXP-001',
            'full_name' => 'Expired OTP Agent',
            'email' => 'expired@example.com',
            'phone_number' => '08099887766',
            'is_claimed' => false,
        ]);

        $user = User::factory()->create([
            'email_verified_at' => now(),
            'user_status' => 'active',
        ]);

        $response = $this->actingAs($user)->from(route('agent.register'))->withSession([
            'claim_otp_code_FUWA-EXP-001' => '112233',
            'claim_otp_expires_FUWA-EXP-001' => now()->subMinute(), // expired!
        ])->post(route('agent.register.submit'), [
            'agent_type' => 'existing',
            'company_agent_code' => 'FUWA-EXP-001',
            'claim_otp' => '112233',
            'full_name' => 'Expired OTP Agent',
            'email' => 'expired@example.com',
            'phone_number' => '08099887766',
            'state' => 'Lagos',
            'residential_address' => 'Address',
            'office_address' => 'Address',
            'bvn' => '12345678901',
            'nin' => '10987654321',
            'has_machine' => '1',
            'machine_imei' => '864201041234567',
        ]);

        $response->assertRedirect(route('agent.register'));
        $response->assertSessionHasErrors(['claim_otp']);
    }

    public function test_guest_can_register_as_agent_with_custom_password(): void
    {
        $response = $this->post(route('agent.register.submit'), [
            'agent_type' => 'new',
            'full_name' => 'Guest New Agent',
            'email' => 'guestagent@example.com',
            'password' => 'SecretPass123!',
            'password_confirmation' => 'SecretPass123!',
            'phone_number' => '08099112233',
            'state' => 'Lagos',
            'residential_address' => 'Home Address',
            'office_address' => 'Office Address',
            'bvn' => '12345678901',
            'nin' => '10987654321',
            'has_machine' => '0',
            'machine_imei' => '',
        ]);

        $response->assertRedirect(route('agent.dashboard'));
        $this->assertDatabaseHas('users', [
            'email' => 'guestagent@example.com',
            'fullname' => 'Guest New Agent',
        ]);
    }

    public function test_existing_profile_claim_masks_credentials_and_hydrates_on_submit(): void
    {
        $preApproved = \App\Models\PreApprovedAgent::create([
            'agent_code' => 'FUWA-MASK-001',
            'full_name' => 'Masked Profile Agent',
            'email' => 'maskedagent@example.com',
            'phone_number' => '08012345678',
            'is_claimed' => false,
        ]);

        // 1. Verify search autocomplete returns masked credentials
        $searchResponse = $this->getJson(route('agent.search_preapproved', ['q' => 'Masked']));
        $searchResponse->assertOk();
        $agents = $searchResponse->json('agents');
        $this->assertNotEmpty($agents);
        $found = collect($agents)->firstWhere('agent_code', 'FUWA-MASK-001');
        $this->assertNotNull($found);
        $this->assertStringContainsString('***', $found['email']);
        $this->assertStringContainsString('****', $found['phone_number']);
        $this->assertStringNotContainsString('maskedagent@example.com', $found['email']);
        $this->assertStringNotContainsString('08012345678', $found['phone_number']);

        // 2. Submit registration with the prefilled masked values
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'user_status' => 'active',
        ]);

        $submitResponse = $this->actingAs($user)->withSession([
            'claim_otp_code_FUWA-MASK-001' => '998877',
            'claim_otp_expires_FUWA-MASK-001' => now()->addMinutes(15),
        ])->post(route('agent.register.submit'), [
            'agent_type' => 'existing',
            'company_agent_code' => 'FUWA-MASK-001',
            'claim_otp' => '998877',
            'full_name' => 'Masked Profile Agent',
            'email' => $found['email'], // Masked email prefilled in field
            'phone_number' => $found['phone_number'], // Masked phone prefilled in field
            'state' => 'Lagos',
            'residential_address' => 'Sample Address',
            'office_address' => 'Sample Office',
            'bvn' => '12345678901',
            'nin' => '10987654321',
            'has_machine' => '1',
            'machine_imei' => '864201041234567',
        ]);

        $submitResponse->assertRedirect(route('agent.dashboard'));

        // 3. Confirm enrollment agent has the authentic unmasked details
        $this->assertDatabaseHas('enrollment_agents', [
            'user_id' => $user->id,
            'company_agent_code' => 'FUWA-MASK-001',
            'phone_number' => '08012345678',
            'is_fast_tracked' => true,
        ]);

        $this->assertDatabaseHas('pre_approved_agents', [
            'agent_code' => 'FUWA-MASK-001',
            'is_claimed' => true,
            'claimed_by_user_id' => $user->id,
        ]);
    }
}
