<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\EnrollmentAgent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Owner question 2026-11-28: a WhatsApp message can be copied and sent
 * from another number, so how does the admin know the sender is the real
 * agent? Answer built here: a per-agent activation code that exists only
 * inside the agent's logged-in account, an in-app activation request
 * recorded against that account, and an admin verify step that approves
 * only when the code matches the record.
 */
class AgentActivationVerificationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private EnrollmentAgent $agent;

    protected function setUp(): void
    {
        parent::setUp();

        if (class_exists(\Laravel\Telescope\Telescope::class)) {
            \Laravel\Telescope\Telescope::stopRecording();
        }

        $this->user = User::factory()->create(['kyc_tier' => 3]);
        $this->agent = EnrollmentAgent::create([
            'user_id' => $this->user->id,
            'full_name' => 'Activation Agent',
            'phone_number' => '08099887766',
            'residential_address' => 'Sample Residential 1',
            'office_address' => 'Sample Office 1',
            'bvn' => '12345678901',
            'nin' => '12345678901',
            'machine_imei' => '353095051716654',
            'utility_bill_path' => 'agent_kyc/test-utility-bill.pdf',
            'picture_path' => 'agent_kyc/test-passport.jpg',
            'state' => 'Lagos',
            'status' => 'approved',
            'license_status' => 'paid',
            'license_fee_paid' => 200000,
            'license_paid_at' => now(),
        ]);
    }

    public function test_onboarding_page_generates_a_code_and_puts_it_in_the_whatsapp_message(): void
    {
        $this->assertNull($this->agent->activationCode());

        $response = $this->actingAs($this->user)->get(route('agent.license.onboarding'));
        $response->assertOk();

        $code = $this->agent->fresh()->activationCode();
        $this->assertNotNull($code);
        $this->assertStringStartsWith('ACT-', $code);
        $response->assertSee('Activation+Code', false);
        $response->assertSee($code);
        $response->assertSee('phone number you registered with');
    }

    public function test_agent_records_activation_request_in_app(): void
    {
        $this->actingAs($this->user)
            ->post(route('agent.license.onboarding_activation'))
            ->assertSessionHas('success');

        $fresh = $this->agent->fresh();
        $this->assertSame('requested', $fresh->activationStatus());
        $this->assertNotEmpty(data_get($fresh->meta, 'activation_requested_at'));

        $this->actingAs($this->user)
            ->get(route('agent.license.onboarding'))
            ->assertSee('Activation request recorded');
    }

    public function test_unpaid_agent_cannot_request_activation(): void
    {
        $this->agent->update(['license_status' => 'unpaid', 'license_fee_paid' => 0]);

        $this->actingAs($this->user)
            ->post(route('agent.license.onboarding_activation'))
            ->assertSessionHas('error');

        $this->assertEmpty(data_get($this->agent->fresh()->meta, 'activation_requested_at'));
    }

    public function test_admin_approves_only_when_the_code_matches(): void
    {
        $this->actingAs($this->user)->post(route('agent.license.onboarding_activation'));
        $code = $this->agent->fresh()->activationCode();
        $admin = Admin::factory()->create(['is_super_admin' => true]);

        // Wrong code: refused, nothing changes
        $this->actingAs($admin, 'admin')
            ->post(route('admin.agents.activation_verify', $this->agent->id), ['activation_code' => 'ACT-WRONG99'])
            ->assertSessionHas('error');
        $this->assertSame('requested', $this->agent->fresh()->activationStatus());

        // Correct code (any case): approved and stamped
        $this->actingAs($admin, 'admin')
            ->post(route('admin.agents.activation_verify', $this->agent->id), ['activation_code' => strtolower($code)])
            ->assertSessionHas('success');

        $fresh = $this->agent->fresh();
        $this->assertSame('approved', $fresh->activationStatus());
        $this->assertNotEmpty(data_get($fresh->meta, 'activation_approved_at'));
        $this->assertSame($admin->id, (int) data_get($fresh->meta, 'activation_approved_by'));
    }

    public function test_admin_agent_page_shows_the_activation_verification_block(): void
    {
        $this->actingAs($this->user)->post(route('agent.license.onboarding_activation'));
        $admin = Admin::factory()->create(['is_super_admin' => true]);

        if (class_exists(\Laravel\Telescope\Telescope::class)) {
            \Laravel\Telescope\Telescope::stopRecording();
        }

        $this->actingAs($admin, 'admin')
            ->get(route('admin.agents.show', $this->agent->id))
            ->assertOk()
            ->assertSee('Onboarding Activation')
            ->assertSee('name="activation_code"', false)
            ->assertSee($this->agent->fresh()->activationCode());
    }
}
