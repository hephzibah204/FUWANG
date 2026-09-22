<?php

namespace Tests\Feature;

use App\Models\EnrollmentAgent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewAgentMultiStepOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_phase1_prelogin_signup_logs_user_in_and_redirects_to_onboarding(): void
    {
        $response = $this->post(route('agent.register.submit'), [
            'agent_type' => 'new',
            'full_name' => 'Original User Name',
            'email' => 'newagent@example.com',
            'phone_number' => '08012345678',
            'bvn' => '12345678901',
            'nin' => '10987654321',
            'state' => 'Lagos',
            'residential_address' => '123 Residential Way',
            'office_address' => '456 Office Way',
            'has_machine' => '1',
            'machine_imei' => '864201041112223',
        ]);

        $response->assertRedirect(route('agent.onboarding.index'));
        $this->assertAuthenticated();

        $user = User::where('email', 'newagent@example.com')->first();
        $this->assertNotNull($user);

        $this->assertDatabaseHas('enrollment_agents', [
            'user_id' => $user->id,
            'agent_type' => 'new',
            'state' => 'Lagos',
            'machine_imei' => '864201041112223',
            'onboarding_step' => 'basic_info',
        ]);
    }

    public function test_nin_server_down_fallback_does_not_block_agent_registration(): void
    {
        // Even when NIN verification fails, registration proceeds gracefully
        $response = $this->post(route('agent.register.submit'), [
            'agent_type' => 'new',
            'full_name' => 'Agent Fallback Test',
            'email' => 'fallbackagent@example.com',
            'phone_number' => '08099887766',
            'bvn' => '11111111111',
            'nin' => '22222222222',
            'state' => 'Abuja',
            'residential_address' => 'Res Addr',
            'office_address' => 'Off Addr',
            'has_machine' => '0',
        ]);

        $response->assertRedirect(route('agent.onboarding.index'));
        $this->assertAuthenticated();

        $user = User::where('email', 'fallbackagent@example.com')->first();
        $agent = $user->enrollmentAgent;

        $this->assertNotNull($agent);
        $this->assertEquals('pending_fallback', $agent->nin_server_status);
        $this->assertEquals('basic_info', $agent->onboarding_step);
    }

    public function test_phase2_kyc_document_uploads_and_compliance_terms_acceptance(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'user_status' => 'active',
            'email_verified_at' => now(),
        ]);

        $agent = EnrollmentAgent::create([
            'user_id' => $user->id,
            'agent_type' => 'new',
            'full_name' => 'Agent Onboarding User',
            'phone_number' => '08012345678',
            'state' => 'Lagos',
            'residential_address' => 'Home',
            'office_address' => 'Office',
            'bvn' => '12345678901',
            'nin' => '10987654321',
            'machine_imei' => '864201045556667',
            'status' => 'pending',
            'onboarding_step' => 'basic_info',
        ]);

        // Upload KYC docs
        $utilityBill = UploadedFile::fake()->create('utility.pdf', 500, 'application/pdf');
        $passportPhoto = UploadedFile::fake()->image('passport.jpg');

        $uploadRes = $this->actingAs($user)->post(route('agent.onboarding.upload_docs'), [
            'utility_bill' => $utilityBill,
            'picture' => $passportPhoto,
            'business_registration_number' => 'RC99887766',
        ]);

        $uploadRes->assertRedirect();
        $agent->refresh();
        $this->assertNotNull($agent->utility_bill_path);
        $this->assertNotNull($agent->picture_path);
        $this->assertEquals('RC99887766', $agent->business_registration_number);
        $this->assertEquals('kyc_docs', $agent->onboarding_step);

        // Accept compliance terms
        $complianceRes = $this->actingAs($user)->post(route('agent.onboarding.accept_compliance'), [
            'agree_no_non_appearance' => '1',
            'agree_no_illegal_enrollment' => '1',
            'agree_data_privacy' => '1',
            'agree_no_terminal_tampering' => '1',
            'agree_legal_liability' => '1',
        ]);

        $complianceRes->assertRedirect();
        $agent->refresh();
        $this->assertTrue($agent->accepted_terms);
        $this->assertEquals('submitted', $agent->onboarding_step);
        $this->assertEquals('pending', $agent->status);
    }

    public function test_unauthenticated_registration_with_existing_email_is_rejected_and_does_not_login(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'victim@example.com',
            'user_status' => 'active',
        ]);

        $response = $this->post(route('agent.register.submit'), [
            'agent_type' => 'new',
            'full_name' => 'Attacker Trying Takeover',
            'email' => 'victim@example.com',
            'phone_number' => '08099881122',
            'bvn' => '11111111111',
            'nin' => '22222222222',
            'state' => 'Abuja',
            'residential_address' => 'Attacker Res',
            'office_address' => 'Attacker Off',
            'has_machine' => '0',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }
}
