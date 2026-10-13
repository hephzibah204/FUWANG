<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AgentLicenseTransaction;
use App\Models\SystemSetting;
use App\Models\VerificationResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminInvestmentVsMadeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Http::preventStrayRequests();
    }

    public function test_capital_investment_update_requires_admin_auth(): void
    {
        $response = $this->post(route('admin.capital_investment.update'), [
            'amount' => 50000,
        ]);

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_update_capital_investment(): void
    {
        $admin = Admin::factory()->create([
            'is_super_admin' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.capital_investment.update'), [
            'amount' => 150000.50,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Capital investment updated to NGN 150,000.50.');

        $this->assertEquals(150000.50, (float) SystemSetting::get('total_capital_invested'));
    }

    public function test_capital_investment_validates_input(): void
    {
        $admin = Admin::factory()->create([
            'is_super_admin' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.capital_investment.update'), [
            'amount' => -100,
        ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_admin_dashboard_displays_investment_vs_made_kpis_and_ratio(): void
    {
        $admin = Admin::factory()->create([
            'is_super_admin' => true,
        ]);

        SystemSetting::set('total_capital_invested', 50000, 'number');

        Http::fake([
            'https://dataverify.org/api/developers/balance.php*' => Http::response([
                'status' => 'success',
                'balance' => '10000.00',
            ], 200),
            'https://robosttech.com/api/balance*' => Http::response([
                'status' => 'success',
                'balance' => '20000.00',
            ], 200),
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.dashboard', ['refresh' => 1]));

        $response->assertStatus(200);
        $response->assertSee('Investment vs. Revenue Analysis');
        $response->assertSee('Total Invested');
        $response->assertSee('Total Made');
        $response->assertSee('Net Profit');
        $response->assertSee('Return on Investment');
        $response->assertSee('NGN 50,000.00');
        $response->assertSee('editInvestmentModal');
        $response->assertSee('Save Capital Investment');
    }
}
