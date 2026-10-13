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

    public function test_dataverify_balance_check_uses_headers_without_query_params(): void
    {
        $admin = Admin::factory()->create([
            'is_super_admin' => true,
        ]);

        ApiCenter::create([
            'dataverify_api_key' => 'secret_dv_token_xyz',
            'robosttech_api_key' => 'robost_token_xyz',
        ]);

        Http::fake([
            'https://dataverify.org/api/developers/balance.php' => function (\Illuminate\Http\Client\Request $request) {
                // Ensure query string does NOT have api_key (DataVerify rejection criterion)
                $query = parse_url($request->url(), PHP_URL_QUERY);
                $hasQuery = !empty($query);

                $authHeader = $request->header('Authorization')[0] ?? '';
                $xApiKey = $request->header('X-API-Key')[0] ?? '';

                if ($hasQuery) {
                    return Http::response([
                        'status' => false,
                        'message' => 'Do not send the API key in the query string. Use the Authorization: Bearer header, the X-API-Key header, or a JSON body.',
                    ], 400);
                }

                if ($authHeader === 'Bearer secret_dv_token_xyz' && $xApiKey === 'secret_dv_token_xyz') {
                    return Http::response([
                        'status' => 'success',
                        'balance' => '95400.00',
                    ], 200);
                }

                return Http::response(['status' => false, 'message' => 'Unauthorized'], 401);
            },
            'https://robosttech.com/api/*' => Http::response([
                'status' => 'success',
                'balance' => '33000.00',
            ], 200),
        ]);

        $response = $this->actingAs($admin, 'admin')->getJson(route('admin.provider_balances', ['refresh' => 1]));

        $response->assertStatus(200);
        $response->assertJsonPath('dataverify.status', 'ok');
        $response->assertJsonPath('dataverify.balance', 95400);
        $response->assertJsonPath('dataverify.formatted', 'NGN 95,400.00');
    }

    public function test_robosttech_balance_supports_wallet_fallback_and_formatted_currencies(): void
    {
        $admin = Admin::factory()->create([
            'is_super_admin' => true,
        ]);

        ApiCenter::create([
            'dataverify_api_key' => 'dv_key',
            'robosttech_api_key' => 'robost_key',
            'robosttech_endpoint_validation' => 'https://robosttech.com/api/validation',
        ]);

        Http::fake([
            'https://dataverify.org/api/developers/balance.php' => Http::response([
                'status' => 'success',
                'balance' => '50000.00',
            ], 200),
            // /balance returns 404, fallback to /wallet returns formatted currency
            'https://robosttech.com/api/balance' => Http::response(['message' => 'Not Found'], 404),
            'https://robosttech.com/api/wallet' => Http::response([
                'status' => 'success',
                'wallet_balance' => 'NGN 68,500.00',
            ], 200),
        ]);

        $response = $this->actingAs($admin, 'admin')->getJson(route('admin.provider_balances', ['refresh' => 1]));

        $response->assertStatus(200);
        $response->assertJsonPath('robosttech.status', 'ok');
        $response->assertJsonPath('robosttech.balance', 68500);
        $response->assertJsonPath('robosttech.formatted', 'NGN 68,500.00');
    }

    public function test_admin_can_update_api_credentials_from_dashboard(): void
    {
        $admin = Admin::factory()->create([
            'is_super_admin' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.api_credentials.update'), [
            'dataverify_api_key' => 'new_dv_key_live',
            'robosttech_api_key' => 'new_robost_key_live',
            'robosttech_endpoint_nin' => 'https://robosttech.com/api',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('api_centers', [
            'dataverify_api_key' => 'new_dv_key_live',
            'robosttech_api_key' => 'new_robost_key_live',
        ]);
    }
}
