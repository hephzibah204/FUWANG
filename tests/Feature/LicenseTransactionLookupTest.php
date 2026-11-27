<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AgentLicenseTransaction;
use App\Models\EnrollmentAgent;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LicenseTransactionLookupTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private User $agentUser;
    private EnrollmentAgent $agent;
    private AgentLicenseTransaction $txn;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create(['is_super_admin' => true]);

        SystemSetting::set('agent_license_regular_price', 200000);
        SystemSetting::set('agent_license_promo_ends_at', '2026-01-01 00:00:00');

        config(['services.paystack.public' => 'pk_test_123456789']);
        config(['services.paystack.secret' => 'sk_test_987654321']);

        $this->agentUser = User::factory()->create(['kyc_tier' => 3]);
        $this->agent = EnrollmentAgent::create([
            'user_id' => $this->agentUser->id,
            'full_name' => 'Lookup Testing Agent',
            'phone_number' => '08099887766',
            'residential_address' => 'Sample Residential 1',
            'office_address' => 'Sample Office 1',
            'bvn' => '12345678901',
            'nin' => '12345678901',
            'state' => 'Lagos',
            'status' => 'approved',
            'license_status' => 'paid',
            'license_payment_method' => 'paystack',
            'license_payment_reference' => 'LIC-20261127-1-ABC123',
            'license_fee_paid' => 180000,
            'license_paid_at' => now(),
        ]);

        $this->txn = AgentLicenseTransaction::create([
            'agent_id' => $this->agent->id,
            'user_id' => $this->agentUser->id,
            'amount' => 180000,
            'payment_method' => 'paystack',
            'reference' => 'LIC-20261127-1-ABC123',
            'gateway_reference' => '3847562019',
            'status' => 'completed',
        ]);
    }

    private function fakePaystackVerify(int $amountKobo, string $status = 'success'): void
    {
        Http::fake([
            'https://api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => [
                    'id' => 3847562019,
                    'status' => $status,
                    'reference' => 'LIC-20261127-1-ABC123',
                    'amount' => $amountKobo,
                    'channel' => 'card',
                    'paid_at' => '2026-11-27T10:15:00.000Z',
                    'customer' => ['email' => $this->agentUser->email],
                    'gateway_response' => 'Successful',
                ],
            ], 200),
        ]);
    }

    public function test_lookup_page_renders_search_form(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.agents.licenses.txn_lookup'))
            ->assertOk()
            ->assertSee('Match a Paystack Transaction')
            ->assertSee('Match Transaction');
    }

    public function test_admin_can_match_by_paystack_transaction_id(): void
    {
        $this->fakePaystackVerify(18000000);

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.agents.licenses.txn_lookup', ['q' => '3847562019']))
            ->assertOk()
            ->assertSee('Transaction Matched')
            ->assertSee('Lookup Testing Agent')
            ->assertSee('3847562019')
            ->assertSee('LIC-20261127-1-ABC123');
    }

    public function test_admin_can_match_by_fuwa_reference_and_amount_mismatch_is_flagged(): void
    {
        $this->fakePaystackVerify(10000000); // ₦100,000 on Paystack vs ₦180,000 in Fuwa

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.agents.licenses.txn_lookup', ['q' => 'LIC-20261127-1-ABC123']))
            ->assertOk()
            ->assertSee('Records Do Not Fully Match')
            ->assertSee('Amount differs');
    }

    public function test_lookup_finds_paystack_transaction_with_no_fuwa_record(): void
    {
        Http::fake([
            'https://api.paystack.co/transaction/*' => Http::response([
                'status' => true,
                'data' => [
                    'id' => 555000111,
                    'status' => 'success',
                    'reference' => 'LIC-20261120-9-ZZZ999',
                    'amount' => 20000000,
                    'channel' => 'bank_transfer',
                    'paid_at' => '2026-11-20T09:00:00.000Z',
                    'customer' => ['email' => 'unknown-agent@example.com'],
                ],
            ], 200),
        ]);

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.agents.licenses.txn_lookup', ['q' => '555000111']))
            ->assertOk()
            ->assertSee('Found on Paystack, but no Fuwa license record')
            ->assertSee('unknown-agent@example.com');
    }

    public function test_lookup_reports_when_nothing_matches_anywhere(): void
    {
        Http::fake([
            'https://api.paystack.co/transaction/verify/*' => Http::response([
                'status' => false,
                'message' => 'Transaction not found',
            ], 404),
        ]);

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.agents.licenses.txn_lookup', ['q' => 'LIC-UNKNOWN-1']))
            ->assertOk()
            ->assertSee('No matching transaction found');
    }

    public function test_licenses_desk_shows_paystack_id_and_lookup_entry_point(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.agents.licenses.index'))
            ->assertOk()
            ->assertSee('Match a Transaction')
            ->assertSee('Paystack ID: 3847562019');
    }
}
