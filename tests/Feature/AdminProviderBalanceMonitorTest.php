<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\ApiCenter;
use App\Models\CustomApi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminProviderBalanceMonitorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_provider_balances_requires_admin_auth(): void
    {
        $response = $this->get(route('admin.provider_balances'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_fetch_provider_balances_as_json(): void
    {
        $admin = Admin::factory()->create([
            'is_super_admin' => true,
        ]);

        ApiCenter::create([
            'dataverify_api_key' => 'test_dv_key',
            'robosttech_api_key' => 'test_robost_key',
            'robosttech_endpoint_nin' => 'https://robosttech.com/api',
        ]);

        Http::fake([
            'https://dataverify.org/api/developers/balance.php*' => Http::response([
                'status' => 'success',
                'balance' => '75000.00',
            ], 200),
            'https://robosttech.com/api/balance*' => Http::response([
                'status' => 'success',
                'balance' => '42000.50',
            ], 200),
        ]);

        $response = $this->actingAs($admin, 'admin')->getJson(route('admin.provider_balances', ['refresh' => 1]));

        $response->assertStatus(200);
        $response->assertJson([
            'dataverify' => [
                'name' => 'DataVerify',
                'configured' => true,
                'balance' => 75000.00,
                'formatted' => 'NGN 75,000.00',
                'status' => 'ok',
            ],
            'robosttech' => [
                'name' => 'RobostTech',
                'configured' => true,
                'balance' => 42000.50,
                'formatted' => 'NGN 42,000.50',
                'status' => 'ok',
            ],
        ]);
    }

    public function test_admin_dashboard_renders_provider_balance_monitor(): void
    {
        $admin = Admin::factory()->create([
            'is_super_admin' => true,
        ]);

        ApiCenter::create([
            'dataverify_api_key' => 'test_dv_key_2',
            'robosttech_api_key' => 'test_robost_key_2',
        ]);

        Http::fake([
            'https://dataverify.org/api/developers/balance.php*' => Http::response([
                'status' => 'success',
                'balance' => '12000.00',
            ], 200),
            'https://robosttech.com/api/balance*' => Http::response([
                'status' => 'success',
                'balance' => '18500.00',
            ], 200),
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.dashboard', ['refresh' => 1]));

        $response->assertStatus(200);
        $response->assertSee('API Provider Balance Monitor');
        $response->assertSee('DataVerify API');
        $response->assertSee('RobostTech API');
        $response->assertSee('NGN 12,000.00');
        $response->assertSee('NGN 18,500.00');
        $response->assertSee('Check Live Balances');
    }
}
