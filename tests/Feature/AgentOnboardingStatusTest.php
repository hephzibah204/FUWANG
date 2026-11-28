<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\EnrollmentAgent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Owner request 2026-11-28: admin can update an agent's onboarding
 * status to Onboarded, Onboarding Ongoing or Pending Review, and the
 * agent sees the same status on their onboarding page.
 */
class AgentOnboardingStatusTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private EnrollmentAgent $agent;
    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        if (class_exists(\Laravel\Telescope\Telescope::class)) {
            \Laravel\Telescope\Telescope::stopRecording();
        }

        $this->user = User::factory()->create(['kyc_tier' => 3]);
        $this->agent = EnrollmentAgent::create([
            'user_id' => $this->user->id,
            'full_name' => 'Status Agent',
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
        $this->admin = Admin::factory()->create(['is_super_admin' => true]);
    }

    public function test_status_defaults_to_pending_review(): void
    {
        $this->assertSame('pending_review', $this->agent->onboardingStatus());
        $this->assertSame('Pending Review', $this->agent->onboardingStatusLabel());

        $this->actingAs($this->user)
            ->get(route('agent.license.onboarding'))
            ->assertOk()
            ->assertSee('Pending Review');
    }

    public function test_admin_can_move_agent_through_each_status(): void
    {
        foreach (['ongoing' => 'Onboarding Ongoing', 'onboarded' => 'Onboarded', 'pending_review' => 'Pending Review'] as $value => $label) {
            $this->actingAs($this->admin, 'admin')
                ->post(route('admin.agents.onboarding_status', $this->agent->id), ['onboarding_status' => $value])
                ->assertSessionHas('success');

            $fresh = $this->agent->fresh();
            $this->assertSame($value, $fresh->onboardingStatus());
            $this->assertSame($label, $fresh->onboardingStatusLabel());
            $this->assertSame($this->admin->id, (int) data_get($fresh->meta, 'onboarding_status_by'));
        }
    }

    public function test_agent_sees_the_status_admin_set(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.agents.onboarding_status', $this->agent->id), ['onboarding_status' => 'ongoing']);

        $this->actingAs($this->user)
            ->get(route('agent.license.onboarding'))
            ->assertOk()
            ->assertSee('Onboarding Ongoing');
    }

    public function test_invalid_status_is_rejected(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.agents.onboarding_status', $this->agent->id), ['onboarding_status' => 'live'])
            ->assertSessionHasErrors('onboarding_status');

        $this->assertSame('pending_review', $this->agent->fresh()->onboardingStatus());
    }

    public function test_admin_page_shows_the_status_controls(): void
    {
        if (class_exists(\Laravel\Telescope\Telescope::class)) {
            \Laravel\Telescope\Telescope::stopRecording();
        }

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.agents.show', $this->agent->id))
            ->assertOk()
            ->assertSee('Onboarding status:')
            ->assertSee('name="onboarding_status"', false);
    }
}
