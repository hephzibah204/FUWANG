<?php

namespace Tests\Feature;

use App\Models\AccountBalance;
use App\Models\Admin;
use App\Models\AgentLicenseTransaction;
use App\Models\EnrollmentAgent;
use App\Models\PaymentWebhookEvent;
use App\Models\PreApprovedAgent;
use App\Models\SystemSetting;
use App\Models\User;
use App\Jobs\ProcessPaymentWebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AgentLicensePaymentTest extends TestCase
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

        // Default Promo pricing: 100,000 promo price ending October 10th, 2026
        SystemSetting::set('agent_license_promo_price', 100000);
        SystemSetting::set('agent_license_regular_price', 250000);
        SystemSetting::set('agent_license_promo_ends_at', '2026-10-10 23:59:59');

        // Configure Paystack test keys
        config(['services.paystack.public' => 'pk_test_123456789']);
        config(['services.paystack.secret' => 'sk_test_987654321']);

        // Set user to KYC Tier 2 so high amount transactions pass KYC check
        $this->agentUser = User::factory()->create([
            'kyc_tier' => 2,
        ]);

        AccountBalance::create([
            'user_id' => $this->agentUser->id,
            'email' => $this->agentUser->email,
            'user_balance' => 150000,
        ]);

        $this->agent = EnrollmentAgent::create([
            'user_id' => $this->agentUser->id,
            'full_name' => 'License Testing Agent',
            'phone_number' => '08099887766',
            'residential_address' => 'Sample Residential 1',
            'office_address' => 'Sample Office 1',
            'bvn' => '12345678901',
            'nin' => '12345678901',
            'state' => 'Lagos',
            'status' => 'pending',
            'license_status' => 'unpaid',
        ]);
    }

    public function test_agent_can_fetch_license_info_with_promo_fee(): void
    {
        $response = $this->actingAs($this->agentUser)
            ->getJson(route('agent.license.info'));

        $response->assertOk()
            ->assertJson([
                'status' => true,
                'pricing' => [
                    'effective_fee' => 100000,
                    'is_promo' => true,
                ],
                'agent' => [
                    'license_status' => 'unpaid',
                ],
            ]);
    }

    public function test_agent_can_initialize_paystack_payment_with_metadata(): void
    {
        $response = $this->actingAs($this->agentUser)
            ->postJson(route('agent.license.paystack_init'));

        $response->assertOk()
            ->assertJson([
                'status' => true,
                'amount_kobo' => 10000000, // 100,000 * 100
                'public_key' => 'pk_test_123456789',
            ]);

        $this->assertStringStartsWith('LIC-', $response->json('reference'));
    }

    public function test_agent_can_verify_paystack_and_automatically_be_accredited(): void
    {
        $ref = 'LIC-' . strtoupper(uniqid());

        Http::fake([
            'https://api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'reference' => $ref,
                    'amount' => 10000000, // 100,000 in kobo
                    'channel' => 'card',
                    'customer' => ['email' => $this->agentUser->email],
                    'metadata' => [
                        'payment_type' => 'agent_license',
                        'agent_id' => $this->agent->id,
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->agentUser)
            ->postJson(route('agent.license.paystack_verify'), [
                'reference' => $ref,
            ]);

        $response->assertOk()
            ->assertJson([
                'status' => true,
            ]);

        $this->agent->refresh();
        $this->assertEquals('paid', $this->agent->license_status);
        $this->assertEquals('paystack', $this->agent->license_payment_method);
        $this->assertEquals(100000, (float)$this->agent->license_fee_paid);

        $this->assertDatabaseHas('agent_license_transactions', [
            'agent_id' => $this->agent->id,
            'reference' => $ref,
            'payment_method' => 'paystack',
            'status' => 'completed',
        ]);
    }

    public function test_agent_can_pay_for_license_via_wallet_balance(): void
    {
        $balanceRecord = AccountBalance::where('user_id', $this->agentUser->id)->first();
        $initialBalance = (float) $balanceRecord->user_balance;

        $response = $this->actingAs($this->agentUser)
            ->post(route('agent.license.pay_wallet'));

        $response->assertRedirect()
            ->assertSessionHas('success');

        $this->agent->refresh();
        $this->assertEquals('paid', $this->agent->license_status);
        $this->assertEquals('wallet', $this->agent->license_payment_method);
        $this->assertEquals(100000, (float)$this->agent->license_fee_paid);

        // Balance should be deducted by 100,000
        $balanceRecord->refresh();
        $this->assertEquals($initialBalance - 100000, (float)$balanceRecord->user_balance);

        $this->assertDatabaseHas('agent_license_transactions', [
            'agent_id' => $this->agent->id,
            'payment_method' => 'wallet',
            'status' => 'completed',
            'amount' => 100000,
        ]);
    }

    public function test_agent_wallet_payment_fails_if_insufficient_balance(): void
    {
        $poorUser = User::factory()->create(['kyc_tier' => 2]);
        AccountBalance::create([
            'user_id' => $poorUser->id,
            'email' => $poorUser->email,
            'user_balance' => 5000,
        ]);

        $poorAgent = EnrollmentAgent::create([
            'user_id' => $poorUser->id,
            'full_name' => 'Poor Agent',
            'phone_number' => '08011223344',
            'residential_address' => 'Sample Residential 2',
            'office_address' => 'Sample Office 2',
            'bvn' => '12345678902',
            'nin' => '12345678902',
            'status' => 'pending',
            'license_status' => 'unpaid',
        ]);

        $response = $this->actingAs($poorUser)
            ->post(route('agent.license.pay_wallet'));

        $response->assertRedirect()
            ->assertSessionHas('error');

        $this->assertEquals('unpaid', $poorAgent->fresh()->license_status);
    }

    public function test_agent_can_upload_offline_proof_and_status_becomes_pending_review(): void
    {
        $file = UploadedFile::fake()->create('bank_receipt.pdf', 300, 'application/pdf');

        $response = $this->actingAs($this->agentUser)
            ->post(route('agent.license.upload_proof'), [
                'amount_paid' => 100000,
                'bank_name' => 'Zenith Bank',
                'payment_date' => '2026-10-07',
                'transaction_reference' => 'ZENITH-OFFLINE-992211',
                'payment_proof' => $file,
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $this->agent->refresh();
        $this->assertEquals('pending_review', $this->agent->license_status);
        $this->assertEquals('offline_proof', $this->agent->license_payment_method);
        $this->assertEquals('ZENITH-OFFLINE-992211', $this->agent->license_payment_reference);
        $this->assertNotNull($this->agent->license_proof_path);
    }

    public function test_agent_can_submit_legacy_pre_launch_payment_claim(): void
    {
        $response = $this->actingAs($this->agentUser)
            ->post(route('agent.license.claim_legacy'), [
                'legacy_payment_date' => '2026-08-15',
                'legacy_reference' => 'OLD-SLIP-7711',
                'legacy_notes' => 'Paid cash/teller at coordinator office before website launch',
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $this->agent->refresh();
        $this->assertEquals('pending_review', $this->agent->license_status);
        $this->assertEquals('legacy_pre_platform', $this->agent->license_payment_method);
    }

    public function test_admin_can_approve_offline_proof(): void
    {
        $this->agent->update([
            'license_status' => 'pending_review',
            'license_payment_method' => 'offline_proof',
            'license_payment_reference' => 'ZENITH-OFFLINE-992211',
            'license_fee_amount' => 100000,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.agents.licenses.approve_proof', $this->agent->id), [
                'admin_notes' => 'Verified on bank statement.',
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $this->agent->refresh();
        $this->assertEquals('paid', $this->agent->license_status);
        $this->assertEquals($this->admin->id, $this->agent->license_verified_by);
        $this->assertNotNull($this->agent->license_paid_at);
    }

    public function test_admin_can_reject_offline_proof(): void
    {
        $this->agent->update([
            'license_status' => 'pending_review',
            'license_payment_method' => 'offline_proof',
            'license_payment_reference' => 'INVALID-REF',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.agents.licenses.reject_proof', $this->agent->id), [
                'rejection_reason' => 'Transaction not found in statement.',
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $this->agent->refresh();
        $this->assertEquals('unpaid', $this->agent->license_status);
        $this->assertEquals('Transaction not found in statement.', $this->agent->license_rejection_reason);
    }

    public function test_admin_can_manually_mark_agent_as_paid_for_license(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.agents.licenses.mark_paid', $this->agent->id), [
                'payment_method' => 'legacy_pre_platform',
                'amount' => 100000,
                'payment_reference' => 'LEGACY-PAID-001',
                'admin_notes' => 'Confirmed from legacy ledger records.',
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $this->agent->refresh();
        $this->assertEquals('paid', $this->agent->license_status);
        $this->assertEquals('legacy_pre_platform', $this->agent->license_payment_method);
        $this->assertEquals(100000, (float)$this->agent->license_fee_paid);
    }

    public function test_admin_can_approve_agent_regardless_of_unpaid_license_status(): void
    {
        // Agent license is unpaid
        $this->assertEquals('unpaid', $this->agent->license_status);
        $this->assertEquals('pending', $this->agent->status);

        // Admin decides to approve agent anyway (approval is at admin discretion)
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.agents.approve', $this->agent->id));

        $response->assertRedirect()
            ->assertSessionHas('success');

        $this->agent->refresh();
        $this->assertEquals('approved', $this->agent->status);
        $this->assertEquals('unpaid', $this->agent->license_status);
    }

    public function test_pre_approved_agent_with_paid_license_roster_auto_inherits_paid_status(): void
    {
        PreApprovedAgent::create([
            'agent_code' => 'AGT-PRE-991',
            'full_name' => 'Roster Pre-Paid Agent',
            'email' => 'roster_prepaid@fuwa.ng',
            'phone_number' => '08055667788',
            'has_paid_license' => true,
            'license_payment_method' => 'legacy_pre_platform',
            'license_notes' => 'Pre-paid ₦100,000 via roster batch.',
        ]);

        $rosterUser = User::factory()->create([
            'email' => 'roster_prepaid@fuwa.ng',
            'number' => '08055667788',
        ]);

        $response = $this->actingAs($rosterUser)
            ->withSession([
                'claim_otp_code_AGT-PRE-991' => '123456',
                'claim_otp_expires_AGT-PRE-991' => now()->addMinutes(15),
            ])
            ->post(route('agent.register.submit'), [
                'agent_type' => 'existing',
                'company_agent_code' => 'AGT-PRE-991',
                'claim_otp' => '123456',
                'full_name' => 'Roster Pre-Paid Agent',
                'phone_number' => '08055667788',
                'email' => 'roster_prepaid@fuwa.ng',
                'residential_address' => 'Sample Street 1',
                'office_address' => 'Sample Office 1',
                'bvn' => '12345678901',
                'nin' => '12345678901',
                'state' => 'Lagos',
                'has_machine' => 1,
                'machine_imei' => '123456789012345',
            ]);

        $agent = EnrollmentAgent::where('user_id', $rosterUser->id)->first();
        $this->assertNotNull($agent);
        $this->assertEquals('paid', $agent->license_status);
        $this->assertEquals('legacy_pre_platform', $agent->license_payment_method);
        $this->assertEquals(100000, (float)$agent->license_fee_paid);
    }

    public function test_webhook_automatically_accredits_license_without_crediting_wallet(): void
    {
        $balanceRecord = AccountBalance::where('user_id', $this->agentUser->id)->first();
        $initialBalance = (float) $balanceRecord->user_balance;
        $ref = 'LIC-WEBHOOK-' . strtoupper(uniqid());

        $payload = [
            'event' => 'charge.success',
            'data' => [
                'reference' => $ref,
                'status' => 'success',
                'currency' => 'NGN',
                'amount' => 10000000, // 100k in kobo
                'customer' => ['email' => $this->agentUser->email],
                'metadata' => [
                    'payment_type' => 'agent_license',
                    'agent_id' => $this->agent->id,
                ],
            ],
        ];

        $event = PaymentWebhookEvent::create([
            'provider' => 'paystack',
            'event_type' => 'charge.success',
            'reference' => $ref,
            'amount' => 100000.00,
            'email' => $this->agentUser->email,
            'payload' => $payload,
            'signature_valid' => true,
            'processing_status' => 'pending',
        ]);

        $job = new ProcessPaymentWebhookEvent($event->id);
        $job->handle();

        $this->agent->refresh();
        $this->assertEquals('paid', $this->agent->license_status);
        $this->assertEquals('paystack', $this->agent->license_payment_method);
        $this->assertEquals(100000, (float)$this->agent->license_fee_paid);

        // Wallet balance must NOT be credited with 100,000
        $balanceRecord->refresh();
        $this->assertEquals($initialBalance, (float)$balanceRecord->user_balance);
    }

    public function test_admin_can_update_license_details_to_correct_mistake_in_price_and_status(): void
    {
        // Initially marked paid at 100,000
        $this->agent->update([
            'license_status' => 'paid',
            'license_fee_paid' => 100000,
            'license_payment_method' => 'admin_manual',
            'license_payment_reference' => 'INITIAL-REF',
        ]);

        // Admin corrects price to 80,000 and updates reference and notes
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.agents.licenses.update', $this->agent->id), [
                'license_status' => 'paid',
                'amount' => 80000.00,
                'payment_method' => 'admin_manual',
                'payment_reference' => 'CORRECTED-REF-99',
                'payment_date' => now()->toDateString(),
                'admin_notes' => 'Corrected amount from 100k to 80k due to ledger typo',
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $this->agent->refresh();
        $this->assertEquals('paid', $this->agent->license_status);
        $this->assertEquals(80000.00, (float) $this->agent->license_fee_paid);
        $this->assertEquals('CORRECTED-REF-99', $this->agent->license_payment_reference);
        $this->assertEquals('Corrected amount from 100k to 80k due to ledger typo', $this->agent->license_admin_notes);
    }

    public function test_admin_can_update_license_status_from_paid_to_unpaid_when_marked_by_mistake(): void
    {
        // Initially agent is marked paid
        $this->agent->update([
            'license_status' => 'paid',
            'license_fee_paid' => 100000,
            'license_payment_method' => 'legacy_pre_platform',
            'license_payment_reference' => 'ACCIDENTAL-PAID',
        ]);

        // Admin corrects status to unpaid
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.agents.licenses.update', $this->agent->id), [
                'license_status' => 'unpaid',
                'admin_notes' => 'Marked as paid by mistake - payment was never received',
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $this->agent->refresh();
        $this->assertEquals('unpaid', $this->agent->license_status);
        $this->assertNull($this->agent->license_fee_paid);
        $this->assertNull($this->agent->license_payment_method);
        $this->assertNull($this->agent->license_payment_reference);
    }
}

