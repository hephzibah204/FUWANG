<?php

namespace Tests\Feature;

use App\Models\AccountBalance;
use App\Models\AgentLicenseTransaction;
use App\Models\EnrollmentAgent;
use App\Models\LicenseDiscountCode;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Owner rules 2026-11-27: agents may buy MORE THAN ONE license, and an
 * agent must have uploaded all required documents (Utility Bill +
 * Passport Picture) before paying for a license.
 */
class LicensePurchaseRulesTest extends TestCase
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
        SystemSetting::set('agent_license_promo_price', 100000);
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

        // Agent who already holds ONE license bought for ₦180,000.
        $this->agent = EnrollmentAgent::create([
            'user_id' => $this->user->id,
            'full_name' => 'Multi License Agent',
            'phone_number' => '08099887766',
            'residential_address' => 'Sample Residential 1',
            'office_address' => 'Sample Office 1',
            'bvn' => '12345678901',
            'nin' => '12345678901',
            'utility_bill_path' => 'agent_kyc/test-utility-bill.pdf',
            'picture_path' => 'agent_kyc/test-passport.jpg',
            'state' => 'Lagos',
            'status' => 'approved',
            'license_status' => 'paid',
            'license_fee_amount' => 180000,
            'license_fee_paid' => 180000,
            'license_payment_method' => 'wallet',
            'license_payment_reference' => 'LIC-WLT-FIRST',
            'license_paid_at' => now()->subMonth(),
        ]);

        AgentLicenseTransaction::create([
            'agent_id' => $this->agent->id,
            'user_id' => $this->user->id,
            'amount' => 180000,
            'payment_method' => 'wallet',
            'reference' => 'LIC-WLT-FIRST',
            'status' => 'completed',
        ]);
    }

    // ---------- multiple licenses ----------

    public function test_paid_agent_can_buy_a_second_license_with_wallet(): void
    {
        $this->assertSame(1, $this->agent->licensesOwned());

        $response = $this->actingAs($this->user)->post(route('agent.license.pay_wallet'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $agent = $this->agent->fresh();
        $this->assertSame('paid', $agent->license_status);
        $this->assertEquals(380000.0, (float) $agent->license_fee_paid); // 180k + 200k cumulative
        $this->assertSame(2, $agent->licensesOwned());
        $this->assertSame(2, AgentLicenseTransaction::where('agent_id', $agent->id)->where('status', 'completed')->count());
        $this->assertEquals(300000.0, (float) AccountBalance::where('user_id', $this->user->id)->first()->user_balance);
    }

    public function test_paid_agent_can_buy_a_second_license_via_paystack(): void
    {
        $init = $this->actingAs($this->user)->postJson(route('agent.license.paystack_init'));
        $init->assertOk()->assertJsonPath('status', true)->assertJsonPath('amount', 200000);
        $reference = $init->json('reference');

        Http::fake([
            'https://api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => ['id' => 777001, 'status' => 'success', 'reference' => $reference, 'amount' => 20000000, 'customer' => ['email' => $this->user->email]],
            ], 200),
        ]);

        $verify = $this->actingAs($this->user)->postJson(route('agent.license.paystack_verify'), ['reference' => $reference]);
        $verify->assertOk()->assertJsonPath('status', true);

        $agent = $this->agent->fresh();
        $this->assertEquals(380000.0, (float) $agent->license_fee_paid);
        $this->assertSame(2, $agent->licensesOwned());
    }

    public function test_verifying_the_same_paystack_reference_twice_does_not_double_count(): void
    {
        $init = $this->actingAs($this->user)->postJson(route('agent.license.paystack_init'));
        $reference = $init->json('reference');

        Http::fake([
            'https://api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => ['id' => 777002, 'status' => 'success', 'reference' => $reference, 'amount' => 20000000, 'customer' => ['email' => $this->user->email]],
            ], 200),
        ]);

        $this->actingAs($this->user)->postJson(route('agent.license.paystack_verify'), ['reference' => $reference])->assertOk();
        $second = $this->actingAs($this->user)->postJson(route('agent.license.paystack_verify'), ['reference' => $reference]);
        $second->assertOk()->assertJsonPath('status', true);

        $agent = $this->agent->fresh();
        $this->assertEquals(380000.0, (float) $agent->license_fee_paid); // counted once
        $this->assertSame(2, $agent->licensesOwned());
    }

    public function test_discount_code_stays_single_use_per_agent_across_multiple_licenses(): void
    {
        $code = LicenseDiscountCode::create(['code' => 'ONCEONLY', 'percent' => 10, 'is_active' => true]);

        // First additional license with the code: works (₦180,000).
        $first = $this->actingAs($this->user)->postJson(route('agent.license.pay_wallet'), ['discount_code' => 'ONCEONLY']);
        $first->assertOk()->assertJsonPath('status', true);

        // Same code again for a third license: refused, still one use per agent.
        $second = $this->actingAs($this->user)->postJson(route('agent.license.pay_wallet'), ['discount_code' => 'ONCEONLY']);
        $second->assertStatus(422);
    }

    public function test_paid_agent_dashboard_offers_another_license(): void
    {
        $this->actingAs($this->user)
            ->get(route('agent.dashboard'))
            ->assertOk()
            ->assertSee('Pay for Another License')
            ->assertSee('STATION LICENSE ACTIVE')
            ->assertSee('You Hold 1 Station License');
    }

    // ---------- document gate ----------

    public function test_agent_without_documents_cannot_pay_by_any_channel(): void
    {
        $this->agent->update(['utility_bill_path' => null, 'picture_path' => null, 'license_status' => 'unpaid', 'license_fee_paid' => null]);

        $init = $this->actingAs($this->user)->postJson(route('agent.license.paystack_init'));
        $init->assertStatus(422)->assertJsonPath('status', false);
        $this->assertStringContainsString('Utility Bill', $init->json('message'));
        $this->assertStringContainsString('Passport Picture', $init->json('message'));

        $wallet = $this->actingAs($this->user)->postJson(route('agent.license.pay_wallet'));
        $wallet->assertStatus(422);

        Storage::fake('public');
        $proof = $this->actingAs($this->user)->post(route('agent.license.upload_proof'), [
            'amount_paid' => 200000,
            'bank_name' => 'GTBank',
            'payment_date' => '2026-11-27',
            'transaction_reference' => 'TRF-GATE-1',
            'payment_proof' => UploadedFile::fake()->image('proof.jpg'),
        ]);
        $proof->assertSessionHas('error');
        $this->assertStringContainsString('required documents', session('error'));

        // Nothing was charged or recorded.
        $this->assertEquals(500000.0, (float) AccountBalance::where('user_id', $this->user->id)->first()->user_balance);
        $this->assertSame('unpaid', $this->agent->fresh()->license_status);
    }

    public function test_agent_missing_one_document_is_told_which_one(): void
    {
        $this->agent->update(['picture_path' => null]);

        $response = $this->actingAs($this->user)->postJson(route('agent.license.paystack_init'));
        $response->assertStatus(422);
        $this->assertStringContainsString('Passport Picture', $response->json('message'));
        $this->assertStringNotContainsString('Utility Bill', $response->json('message'));
    }

    public function test_dashboard_points_agents_without_documents_to_upload_first(): void
    {
        $this->agent->update(['utility_bill_path' => null, 'picture_path' => null, 'license_status' => 'unpaid', 'license_fee_paid' => null]);

        $this->actingAs($this->user)
            ->get(route('agent.dashboard'))
            ->assertOk()
            ->assertSee('Upload Required Documents')
            ->assertSee('Documents required first');
    }
}
