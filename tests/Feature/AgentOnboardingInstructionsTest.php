<?php

namespace Tests\Feature;

use App\Models\AccountBalance;
use App\Models\AgentLicenseTransaction;
use App\Models\EnrollmentAgent;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Owner spec 2026-11-27: after an agent pays for their license, the next
 * thing is onboarding — they must read the instructions: (1) get their
 * device ready, (2) request their VPN, (3) get Admin Onboarding details.
 */
class AgentOnboardingInstructionsTest extends TestCase
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

        SystemSetting::set('agent_license_regular_price', 200000);
        SystemSetting::set('agent_license_promo_ends_at', '2026-01-01 00:00:00');
        config(['services.paystack.public' => 'pk_test_123456789']);
        config(['services.paystack.secret' => 'sk_test_987654321']);

        $this->user = User::factory()->create(['kyc_tier' => 3]);
        AccountBalance::create([
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'user_balance' => 500000,
            'api_key' => 'user',
        ]);

        $this->agent = EnrollmentAgent::create([
            'user_id' => $this->user->id,
            'full_name' => 'Onboarding Agent',
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
            'license_status' => 'unpaid',
        ]);
    }

    private function makePaid(): void
    {
        $this->agent->update([
            'license_status' => 'paid',
            'license_fee_paid' => 200000,
            'license_payment_method' => 'wallet',
            'license_payment_reference' => 'LIC-WLT-ONB-1',
            'license_paid_at' => now(),
        ]);
        AgentLicenseTransaction::create([
            'agent_id' => $this->agent->id,
            'user_id' => $this->user->id,
            'amount' => 200000,
            'payment_method' => 'wallet',
            'reference' => 'LIC-WLT-ONB-1',
            'status' => 'completed',
        ]);
    }

    public function test_unpaid_agent_cannot_open_onboarding_instructions(): void
    {
        $this->actingAs($this->user)
            ->get(route('agent.license.onboarding'))
            ->assertRedirect(route('agent.dashboard'));
    }

    public function test_paid_agent_sees_all_three_onboarding_steps(): void
    {
        $this->makePaid();

        $this->actingAs($this->user)
            ->get(route('agent.license.onboarding'))
            ->assertOk()
            ->assertSee('Station Onboarding Instructions')
            ->assertSee('Get Your Device Ready')
            ->assertSee('*#06#')
            ->assertSee('353095051716654')
            ->assertSee('Request Your VPN')
            ->assertSee('Get Your Admin Onboarding Details')
            ->assertSee('Lagos State Desk Coordinator')
            ->assertSee('support@fuwa.ng')
            ->assertSee('I Have Read These Instructions');
    }

    public function test_agent_can_request_vpn_and_it_is_recorded(): void
    {
        $this->makePaid();

        $this->actingAs($this->user)
            ->post(route('agent.license.onboarding_vpn'))
            ->assertSessionHas('success');

        $meta = $this->agent->fresh()->meta;
        $this->assertNotEmpty(data_get($meta, 'vpn_requested_at'));
        $this->assertSame('requested', data_get($meta, 'vpn_status'));

        $this->actingAs($this->user)
            ->get(route('agent.license.onboarding'))
            ->assertOk()
            ->assertSee('VPN requested');
    }

    public function test_agent_can_confirm_reading_the_instructions(): void
    {
        $this->makePaid();

        $this->actingAs($this->user)
            ->post(route('agent.license.onboarding_read'))
            ->assertSessionHas('success');

        $this->assertNotEmpty(data_get($this->agent->fresh()->meta, 'onboarding_read_at'));
    }

    public function test_first_wallet_payment_sends_agent_to_onboarding(): void
    {
        $response = $this->actingAs($this->user)->post(route('agent.license.pay_wallet'));

        $response->assertRedirect(route('agent.license.onboarding'));
        $response->assertSessionHas('success');
        $this->assertSame('paid', $this->agent->fresh()->license_status);
    }

    public function test_second_wallet_payment_stays_on_dashboard_flow(): void
    {
        $this->makePaid();

        $response = $this->actingAs($this->user)->post(route('agent.license.pay_wallet'));

        $response->assertSessionHas('success');
        $this->assertNotSame(route('agent.license.onboarding'), $response->headers->get('Location'));
        $this->assertSame(2, $this->agent->fresh()->licensesOwned());
    }

    public function test_first_paystack_payment_redirects_to_onboarding(): void
    {
        $init = $this->actingAs($this->user)->postJson(route('agent.license.paystack_init'));
        $init->assertOk();
        $reference = $init->json('reference');

        Http::fake([
            'https://api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => [
                    'id' => 888001,
                    'status' => 'success',
                    'reference' => $reference,
                    'amount' => 20000000,
                    'customer' => ['email' => $this->user->email],
                ],
            ], 200),
        ]);

        $verify = $this->actingAs($this->user)->postJson(route('agent.license.paystack_verify'), ['reference' => $reference]);
        $verify->assertOk()->assertJsonPath('redirect', route('agent.license.onboarding'));
    }
}
