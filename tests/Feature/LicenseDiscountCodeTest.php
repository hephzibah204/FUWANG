<?php

namespace Tests\Feature;

use App\Models\AccountBalance;
use App\Models\Admin;
use App\Models\EnrollmentAgent;
use App\Models\LicenseDiscountCode;
use App\Models\LicenseDiscountRedemption;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\LicenseDiscountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LicenseDiscountCodeTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private User $agentUser;
    private EnrollmentAgent $agent;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->admin = Admin::factory()->create(['is_super_admin' => true]);

        // License fee is ₦200,000 with NO promo running (promo ended 2026-01-01).
        SystemSetting::set('agent_license_regular_price', 200000);
        SystemSetting::set('agent_license_promo_price', 150000);
        SystemSetting::set('agent_license_promo_ends_at', '2026-01-01 00:00:00');

        config(['services.paystack.public' => 'pk_test_123456789']);
        config(['services.paystack.secret' => 'sk_test_987654321']);

        $this->agentUser = User::factory()->create(['kyc_tier' => 3]);
        AccountBalance::create([
            'user_id' => $this->agentUser->id,
            'email' => $this->agentUser->email,
            'user_balance' => 250000,
        ]);

        $this->agent = EnrollmentAgent::create([
            'user_id' => $this->agentUser->id,
            'full_name' => 'Discount Testing Agent',
            'utility_bill_path' => 'agent_kyc/test-utility-bill.pdf',
            'picture_path' => 'agent_kyc/test-passport.jpg',
            'phone_number' => '08099887766',
            'residential_address' => 'Sample Residential 1',
            'office_address' => 'Sample Office 1',
            'bvn' => '12345678901',
            'nin' => '12345678901',
            'state' => 'Lagos',
            'status' => 'approved',
            'license_status' => 'unpaid',
        ]);
    }

    private function makeCode(array $attrs = []): LicenseDiscountCode
    {
        return LicenseDiscountCode::create(array_merge([
            'code' => 'WELCOME10',
            'percent' => 10,
            'is_active' => true,
        ], $attrs));
    }

    private function makeSecondAgent(): array
    {
        $user = User::factory()->create(['kyc_tier' => 3]);
        AccountBalance::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'user_balance' => 250000,
        ]);
        $agent = EnrollmentAgent::create([
            'user_id' => $user->id,
            'full_name' => 'Second Agent',
            'utility_bill_path' => 'agent_kyc/test-utility-bill.pdf',
            'picture_path' => 'agent_kyc/test-passport.jpg',
            'phone_number' => '08011112222',
            'residential_address' => 'Sample Residential 2',
            'office_address' => 'Sample Office 2',
            'bvn' => '10987654321',
            'nin' => '10987654321',
            'state' => 'Ogun',
            'status' => 'approved',
            'license_status' => 'unpaid',
        ]);

        return [$user, $agent];
    }

    public function test_admin_can_create_discount_code_and_code_is_uppercased(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.agents.licenses.discount_codes.store'), [
                'code' => 'welcome10',
                'percent' => 10,
            ]);

        $response->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('license_discount_codes', [
            'code' => 'WELCOME10',
            'percent' => 10,
            'is_active' => 1,
        ]);
    }

    public function test_admin_create_code_auto_generates_when_blank_and_rejects_duplicates_and_bad_percent(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.agents.licenses.discount_codes.store'), ['percent' => 15])
            ->assertSessionHas('success');
        $this->assertEquals(1, LicenseDiscountCode::count());
        $this->assertStringStartsWith('FUWA-', LicenseDiscountCode::first()->code);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.agents.licenses.discount_codes.store'), [
                'code' => LicenseDiscountCode::first()->code,
                'percent' => 20,
            ])
            ->assertSessionHasErrors('code');

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.agents.licenses.discount_codes.store'), [
                'code' => 'TOOBIG',
                'percent' => 95,
            ])
            ->assertSessionHasErrors('percent');
    }

    public function test_admin_can_toggle_and_delete_discount_code(): void
    {
        $code = $this->makeCode();

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.agents.licenses.discount_codes.toggle', $code->id))
            ->assertSessionHas('success');
        $this->assertFalse($code->refresh()->is_active);

        $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.agents.licenses.discount_codes.destroy', $code->id))
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('license_discount_codes', ['id' => $code->id]);
    }

    public function test_admin_licenses_page_lists_discount_codes(): void
    {
        $this->makeCode();

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.agents.licenses.index'))
            ->assertOk()
            ->assertSee('License Discount Codes')
            ->assertSee('WELCOME10');
    }

    public function test_agent_can_validate_discount_code_and_sees_discounted_payable(): void
    {
        $this->makeCode();

        $this->actingAs($this->agentUser)
            ->postJson(route('agent.license.discount_validate'), ['discount_code' => 'welcome10'])
            ->assertOk()
            ->assertJson([
                'status' => true,
                'code' => 'WELCOME10',
                'percent' => 10,
                'discount_amount' => 20000,
                'payable' => 180000,
            ]);
    }

    public function test_validate_rejects_unknown_expired_inactive_and_exhausted_codes(): void
    {
        $this->actingAs($this->agentUser)
            ->postJson(route('agent.license.discount_validate'), ['discount_code' => 'NOPE404'])
            ->assertStatus(422)->assertJson(['status' => false]);

        $this->makeCode(['code' => 'OLDCODE', 'expires_at' => now()->subDay()]);
        $this->actingAs($this->agentUser)
            ->postJson(route('agent.license.discount_validate'), ['discount_code' => 'OLDCODE'])
            ->assertStatus(422)->assertJson(['status' => false]);

        $this->makeCode(['code' => 'OFFCODE', 'is_active' => false]);
        $this->actingAs($this->agentUser)
            ->postJson(route('agent.license.discount_validate'), ['discount_code' => 'OFFCODE'])
            ->assertStatus(422)->assertJson(['status' => false]);

        // max_uses = 1 and another agent already holds the reservation
        $limited = $this->makeCode(['code' => 'ONLY1', 'max_uses' => 1]);
        [$otherUser, $otherAgent] = $this->makeSecondAgent();
        app(LicenseDiscountService::class)->reserve($limited, $otherUser, $otherAgent, 'LIC-OTHER-1', 20000);

        $this->actingAs($this->agentUser)
            ->postJson(route('agent.license.discount_validate'), ['discount_code' => 'ONLY1'])
            ->assertStatus(422)->assertJson(['status' => false]);
    }

    public function test_wallet_payment_with_discount_charges_payable_and_records_completed_redemption(): void
    {
        $this->makeCode();

        $response = $this->actingAs($this->agentUser)
            ->post(route('agent.license.pay_wallet'), ['discount_code' => 'WELCOME10']);

        $response->assertRedirect()->assertSessionHas('success');

        $this->agent->refresh();
        $this->assertEquals('paid', $this->agent->license_status);
        $this->assertEquals(180000, (float) $this->agent->license_fee_paid);

        $balance = AccountBalance::where('user_id', $this->agentUser->id)->first();
        $this->assertEquals(70000, (float) $balance->user_balance);

        $this->assertDatabaseHas('agent_license_transactions', [
            'agent_id' => $this->agent->id,
            'payment_method' => 'wallet',
            'status' => 'completed',
            'amount' => 180000,
        ]);

        $redemption = LicenseDiscountRedemption::where('user_id', $this->agentUser->id)->first();
        $this->assertNotNull($redemption);
        $this->assertEquals('completed', $redemption->status);
        $this->assertEquals(20000, (float) $redemption->amount_saved);
    }

    public function test_code_is_unique_per_user_but_reusable_by_other_agents(): void
    {
        $code = $this->makeCode();
        $service = app(LicenseDiscountService::class);

        $service->reserve($code, $this->agentUser, $this->agent, 'LIC-WLT-TEST', 20000, 'completed');

        $mine = $service->findUsable('WELCOME10', $this->agentUser);
        $this->assertFalse($mine['ok']);

        [$otherUser] = $this->makeSecondAgent();
        $theirs = $service->findUsable('WELCOME10', $otherUser);
        $this->assertTrue($theirs['ok']);
    }

    public function test_paystack_checkout_with_discount_verifies_against_discounted_amount(): void
    {
        $this->makeCode();

        $init = $this->actingAs($this->agentUser)
            ->postJson(route('agent.license.paystack_init'), ['discount_code' => 'WELCOME10']);

        $init->assertOk()->assertJson([
            'status' => true,
            'amount' => 180000,
            'amount_kobo' => 18000000,
        ]);
        $reference = $init->json('reference');

        $this->assertDatabaseHas('license_discount_redemptions', [
            'user_id' => $this->agentUser->id,
            'reference' => $reference,
            'status' => 'pending',
        ]);

        Http::fake([
            'https://api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'reference' => $reference,
                    'amount' => 18000000,
                    'channel' => 'card',
                    'customer' => ['email' => $this->agentUser->email],
                ],
            ], 200),
        ]);

        $this->actingAs($this->agentUser)
            ->postJson(route('agent.license.paystack_verify'), ['reference' => $reference])
            ->assertOk()->assertJson(['status' => true]);

        $this->agent->refresh();
        $this->assertEquals('paid', $this->agent->license_status);
        $this->assertEquals(180000, (float) $this->agent->license_fee_paid);
        $this->assertDatabaseHas('license_discount_redemptions', [
            'user_id' => $this->agentUser->id,
            'reference' => $reference,
            'status' => 'completed',
        ]);
    }

    public function test_paystack_verify_rejects_payment_below_discounted_amount(): void
    {
        $this->makeCode();

        $init = $this->actingAs($this->agentUser)
            ->postJson(route('agent.license.paystack_init'), ['discount_code' => 'WELCOME10']);
        $reference = $init->json('reference');

        Http::fake([
            'https://api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'reference' => $reference,
                    'amount' => 10000000, // ₦100,000 < ₦180,000 due
                    'channel' => 'card',
                    'customer' => ['email' => $this->agentUser->email],
                ],
            ], 200),
        ]);

        $this->actingAs($this->agentUser)
            ->postJson(route('agent.license.paystack_verify'), ['reference' => $reference])
            ->assertStatus(422)->assertJson(['status' => false]);

        $this->agent->refresh();
        $this->assertEquals('unpaid', $this->agent->license_status);
    }

    public function test_offline_proof_with_discount_records_expected_fee_and_approval_completes_redemption(): void
    {
        $this->makeCode();
        $file = UploadedFile::fake()->create('bank_receipt.pdf', 300, 'application/pdf');

        $this->actingAs($this->agentUser)
            ->post(route('agent.license.upload_proof'), [
                'amount_paid' => 180000,
                'bank_name' => 'Zenith Bank',
                'payment_date' => '2026-11-27',
                'transaction_reference' => 'ZENITH-DISC-001',
                'payment_proof' => $file,
                'discount_code' => 'WELCOME10',
            ])
            ->assertRedirect()->assertSessionHas('success');

        $this->agent->refresh();
        $this->assertEquals('pending_review', $this->agent->license_status);
        $this->assertEquals(180000, (float) $this->agent->license_fee_amount);
        $this->assertDatabaseHas('license_discount_redemptions', [
            'user_id' => $this->agentUser->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.agents.licenses.approve_proof', $this->agent->id), [
                'admin_notes' => 'Verified on bank statement.',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('license_discount_redemptions', [
            'user_id' => $this->agentUser->id,
            'status' => 'completed',
        ]);
    }

    public function test_admin_rejecting_offline_proof_releases_the_discount_reservation(): void
    {
        $this->makeCode();
        $file = UploadedFile::fake()->create('bank_receipt.pdf', 300, 'application/pdf');

        $this->actingAs($this->agentUser)
            ->post(route('agent.license.upload_proof'), [
                'amount_paid' => 180000,
                'bank_name' => 'Zenith Bank',
                'payment_date' => '2026-11-27',
                'transaction_reference' => 'ZENITH-DISC-002',
                'payment_proof' => $file,
                'discount_code' => 'WELCOME10',
            ])
            ->assertSessionHas('success');

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.agents.licenses.reject_proof', $this->agent->id), [
                'rejection_reason' => 'Receipt is not clear.',
            ])
            ->assertSessionHas('success');

        $this->assertEquals(0, LicenseDiscountRedemption::where('user_id', $this->agentUser->id)->count());
        $retry = app(LicenseDiscountService::class)->findUsable('WELCOME10', $this->agentUser);
        $this->assertTrue($retry['ok']);
    }

    public function test_agent_dashboard_renders_working_license_checkout_with_discount_field(): void
    {
        $this->actingAs($this->agentUser)
            ->get(route('agent.dashboard'))
            ->assertOk()
            ->assertSee('accreditLicenseModal')
            ->assertSee('licenseDiscountCode')
            ->assertSee('discount/validate')
            ->assertSee('transaction_reference')
            ->assertSee('₦200,000.00');
    }
}
