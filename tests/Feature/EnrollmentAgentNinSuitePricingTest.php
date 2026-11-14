<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\EnrollmentAgent;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\VerificationPrice;
use App\Services\Pricing\PriceResolverService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentAgentNinSuitePricingTest extends TestCase
{
    use RefreshDatabase;

    private function createApprovedAgent(User $user): EnrollmentAgent
    {
        return EnrollmentAgent::create([
            'user_id' => $user->id,
            'full_name' => 'Agent Mustapha Danladi',
            'phone_number' => '08031234567',
            'residential_address' => 'No 5 Yakubu Gowon Way, Jos',
            'office_address' => 'Suite 10, Plateau Plaza, Jos',
            'state' => 'Plateau',
            'bvn' => '22334455667',
            'nin' => '11223344556',
            'company_agent_code' => 'PLT-004',
            'status' => 'approved',
            'machine_imei' => '356789012345678',
            'picture_path' => 'agents/photos/mustapha.jpg',
            'nin_verified' => true,
        ]);
    }

    public function test_standard_user_resolves_standard_prices(): void
    {
        $user = User::factory()->create();

        VerificationPrice::updateOrCreate([], [
            'nin_by_nin_price' => 200.0,
            'nin_by_number_price' => 250.0,
            'nin_by_demography_price' => 300.0,
            'validation_price' => 700.0,
            'ipe_clearance_price' => 700.0,
            'personalization_price' => 200.0,
        ]);

        $this->assertEquals(200.0, PriceResolverService::resolve($user, 'nin_by_nin_price'));
        $this->assertEquals(250.0, PriceResolverService::resolve($user, 'nin_by_number_price'));
        $this->assertEquals(700.0, PriceResolverService::resolve($user, 'validation_price'));

        $response = $this->actingAs($user)->get(route('services.nin.suite'));
        $response->assertStatus(200);
        $response->assertDontSee('Enrollment Agent Wholesale (15% Off)');
    }

    public function test_approved_agent_receives_default_15_percent_discount(): void
    {
        $user = User::factory()->create();
        $this->createApprovedAgent($user);

        VerificationPrice::updateOrCreate([], [
            'nin_by_nin_price' => 200.0,
            'nin_by_number_price' => 250.0,
            'nin_by_demography_price' => 300.0,
            'validation_price' => 700.0,
            'ipe_clearance_price' => 700.0,
            'personalization_price' => 200.0,
        ]);

        // 15% discount off standard prices
        // 200 * 0.85 = 170.00
        // 250 * 0.85 = 212.50
        // 300 * 0.85 = 255.00
        // 700 * 0.85 = 595.00
        $this->assertEquals(170.0, PriceResolverService::resolve($user, 'nin_by_nin_price'));
        $this->assertEquals(212.50, PriceResolverService::resolve($user, 'nin_by_number_price'));
        $this->assertEquals(255.0, PriceResolverService::resolve($user, 'nin_by_demography_price'));
        $this->assertEquals(595.0, PriceResolverService::resolve($user, 'validation_price'));
        $this->assertEquals(595.0, PriceResolverService::resolve($user, 'ipe_clearance_price'));
        $this->assertEquals(170.0, PriceResolverService::resolve($user, 'personalization_price'));

        // Visit NIN Suite page
        $suiteResponse = $this->actingAs($user)->get(route('services.nin.suite'));
        $suiteResponse->assertStatus(200);
        $suiteResponse->assertSee('Enrollment Agent Wholesale (15% Off)');
        $suiteResponse->assertSee('₦170.00');
        $suiteResponse->assertSee('₦595.00');

        // Visit Agent Dashboard and check NIN Suite hub & user switcher
        $dashResponse = $this->actingAs($user)->get(route('agent.dashboard'));
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('NIN Suite & Verification Hub', false);
        $dashResponse->assertSee('15% Wholesale Agent Discount Active');
        $dashResponse->assertSee('Switch to User Dashboard');
    }

    public function test_admin_can_set_custom_agent_price_override(): void
    {
        $admin = Admin::factory()->create(['is_super_admin' => true]);
        $agentUser = User::factory()->create();
        $this->createApprovedAgent($agentUser);

        VerificationPrice::updateOrCreate([], [
            'nin_by_nin_price' => 200.0,
            'agent_nin_by_nin_price' => null, // Initially using 15% default discount
        ]);

        $this->assertEquals(170.0, PriceResolverService::resolve($agentUser, 'nin_by_nin_price'));

        // Admin updates pricing in admin settings
        $response = $this->actingAs($admin, 'admin')->post(route('admin.settings.pricing'), [
            'nin_by_nin_price' => 200.0,
            'nin_by_number_price' => 250.0,
            'nin_by_demography_price' => 300.0,
            'bvn_by_bvn' => 200.0,
            'bvn_by_number' => 250.0,
            'verify_by_tracking_id' => 200.0,
            'validation_price' => 700.0,
            'ipe_clearance_price' => 700.0,
            'personalization_price' => 200.0,
            'agent_discount_percentage' => 15,
            'agent_nin_by_nin_price' => 110.0,
            'agent_validation_price' => 550.0,
            'agent_nin_modification_price' => 2100.0,
        ]);

        $response->assertJson(['status' => true]);

        // Override takes precedence over the 15% formula
        $this->assertEquals(110.0, PriceResolverService::resolve($agentUser, 'nin_by_nin_price'));
        $this->assertEquals(550.0, PriceResolverService::resolve($agentUser, 'validation_price'));
        $this->assertEquals(2100.0, PriceResolverService::resolve($agentUser, 'nin_modification_price'));

        // Standard user still pays standard price
        $standardUser = User::factory()->create();
        $this->assertEquals(200.0, PriceResolverService::resolve($standardUser, 'nin_by_nin_price'));
    }

    public function test_admin_custom_discount_percentage_applies_as_fallback(): void
    {
        $agentUser = User::factory()->create();
        $this->createApprovedAgent($agentUser);

        VerificationPrice::updateOrCreate([], [
            'nin_by_nin_price' => 200.0,
            'agent_nin_by_nin_price' => null,
        ]);

        // Default 15% discount
        $this->assertEquals(170.0, PriceResolverService::resolve($agentUser, 'nin_by_nin_price'));

        // Admin updates global discount percentage to 25%
        SystemSetting::set('agent_discount_percentage', '25');

        // 200 * 0.75 = 150.00
        $this->assertEquals(150.0, PriceResolverService::resolve($agentUser, 'nin_by_nin_price'));
    }

    public function test_agent_can_switch_between_agency_and_user_modes(): void
    {
        $agentUser = User::factory()->create();
        $this->createApprovedAgent($agentUser);

        // Switch to user mode
        $response = $this->actingAs($agentUser)->get(route('agent.switch_mode', ['mode' => 'user']));
        $response->assertRedirect(route('dashboard'));
        $this->assertEquals('user', session('active_dashboard_mode'));

        // Switch back to agency mode
        $response = $this->actingAs($agentUser)->get(route('agent.switch_mode', ['mode' => 'agency']));
        $response->assertRedirect(route('agent.dashboard'));
        $this->assertEquals('agency', session('active_dashboard_mode'));
    }
}
