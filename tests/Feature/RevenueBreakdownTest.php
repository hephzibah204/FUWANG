<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AgentLicenseTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Admin\RevenueBreakdownService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Owner request 2026-11-27: admin must differentiate license sales money
 * from VTU sales, verification services and other platform sales. Also
 * guards the fix for wallet license payments being double-counted in
 * total revenue (wallet Transaction row + license transaction row).
 */
class RevenueBreakdownTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (class_exists(\Laravel\Telescope\Telescope::class)) {
            \Laravel\Telescope\Telescope::stopRecording();
        }

        Http::fake();

        $user = User::factory()->create();
        $agent = \App\Models\EnrollmentAgent::create([
            'user_id' => $user->id,
            'full_name' => 'Revenue Agent',
            'phone_number' => '08099887766',
            'residential_address' => 'Sample Residential 1',
            'office_address' => 'Sample Office 1',
            'bvn' => '12345678901',
            'nin' => '12345678901',
            'status' => 'approved',
            'license_status' => 'paid',
        ]);

        $debit = function (string $orderType, float $before, float $after, string $id) use ($user) {
            Transaction::create([
                'user_email' => $user->email,
                'order_type' => $orderType,
                'balance_before' => $before,
                'balance_after' => $after,
                'transaction_id' => $id,
                'status' => 'success',
            ]);
        };

        $debit('Data Purchase', 10000, 9000, 'TX-VTU-1');            // VTU 1,000
        $debit('Cable TV Subscription', 5000, 3000, 'TX-VTU-2');     // VTU 2,000
        $debit('NIN Verification', 8000, 6500, 'TX-VER-1');          // Verification 1,500
        $debit('LOG', 9000, 7000, 'TX-OTH-1');                       // Other 2,000
        $debit('agent_license', 300000, 100000, 'TX-LIC-WALLET');    // wallet license row (must not double-count)
        // A wallet funding credit: not revenue
        Transaction::create([
            'user_email' => $user->email,
            'order_type' => 'Wallet Funding',
            'balance_before' => 0,
            'balance_after' => 50000,
            'transaction_id' => 'TX-FUND-1',
            'status' => 'success',
        ]);

        $license = function (string $method, float $amount, string $status, string $ref) use ($user, $agent) {
            AgentLicenseTransaction::create([
                'agent_id' => $agent->id,
                'user_id' => $user->id,
                'amount' => $amount,
                'payment_method' => $method,
                'reference' => $ref,
                'status' => $status,
            ]);
        };
        $license('wallet', 200000, 'completed', 'LIC-W-1');
        $license('paystack', 200000, 'completed', 'LIC-P-1');
        $license('offline', 150000, 'completed', 'LIC-O-1');
        $license('wallet', 200000, 'pending', 'LIC-PEND-1'); // excluded
    }

    public function test_categories_split_license_vtu_verification_and_other(): void
    {
        $summary = app(RevenueBreakdownService::class)->summary();
        $c = $summary['categories'];

        $this->assertSame(550000.0, $c['licenses']['total']);
        $this->assertSame(3, $c['licenses']['count']);
        $this->assertSame(200000.0, $c['licenses']['methods']['wallet']);
        $this->assertSame(200000.0, $c['licenses']['methods']['paystack']);
        $this->assertSame(150000.0, $c['licenses']['methods']['offline']);

        $this->assertSame(3000.0, $c['vtu']['total']);
        $this->assertSame(1500.0, $c['verification']['total']);
        $this->assertSame(2000.0, $c['other']['total']);

        $this->assertSame(556500.0, $summary['totals']['total']);
        $this->assertSame(556500.0, $summary['totals']['today']); // all fixtures are from today
    }

    public function test_category_classifier(): void
    {
        $service = app(RevenueBreakdownService::class);
        $this->assertSame('vtu', $service->categoryFor('WAEC Result Checker'));
        $this->assertSame('vtu', $service->categoryFor('Airtime Purchase'));
        $this->assertSame('verification', $service->categoryFor('BVN Verification'));
        $this->assertSame('verification', $service->categoryFor('CAC Verify'));
        $this->assertSame('other', $service->categoryFor('LOG'));
    }

    public function test_dashboard_total_revenue_counts_each_license_once(): void
    {
        $admin = Admin::factory()->create(['is_super_admin' => true]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.dashboard', ['refresh' => 1]));
        $response->assertOk();

        $data = $response->viewData('dashboardData');
        // 6,500 of wallet service revenue + 550,000 of license revenue, counted once
        $this->assertSame(556500.0, (float) $data['stats']['total_revenue']);
        $this->assertSame(550000.0, $data['revenue_breakdown']['categories']['licenses']['total']);
        // The doughnut carries the license slice once, as Agent Accreditation
        $this->assertContains('Agent Accreditation', $data['revenueLabels']);
        $this->assertNotContains('agent_license', $data['revenueLabels']);
    }
}
