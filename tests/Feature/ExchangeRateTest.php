<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AccountBalance;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExchangeRateTest extends TestCase
{
    use RefreshDatabase;

    public function test_exchange_currency_uses_real_api_rates()
    {
        $this->withoutMiddleware(\App\Http\Middleware\CheckInstallation::class);
        $user = User::factory()->create();
        AccountBalance::create(['email' => $user->email, 'user_balance' => 0, 'api_key' => 'test']);
        
        SystemSetting::set('app_installed', true);
        SystemSetting::set('services_fx', true);
        SystemSetting::set('fx_fee_percent', 1.5);
        
        // Fake the exchange rate API
        Http::fake([
            'open.er-api.com/*' => Http::response([
                'rates' => [
                    'NGN' => 1600.00
                ]
            ], 200)
        ]);

        $response = $this->actingAs($user)->postJson('/services/fx/exchange', [
            'from_currency' => 'USD',
            'to_currency' => 'NGN',
            'amount' => 100
        ]);

        $response->dump();

        $response->assertStatus(200)
                 ->assertJsonPath('status', true)
                 ->assertJsonPath('rate', 1600);
                 
        // 100 USD * 1600 = 160,000 NGN
        // Fee = 1.5% of 160,000 = 2,400
        // Net = 157,600
        $this->assertDatabaseHas('account_balances', [
            'email' => $user->email,
            'user_balance' => 157600
        ]);
    }

    public function test_exchange_currency_falls_back_to_system_settings_on_api_failure()
    {
        $this->withoutMiddleware(\App\Http\Middleware\CheckInstallation::class);
        $user = User::factory()->create();
        AccountBalance::create(['email' => $user->email, 'user_balance' => 0, 'api_key' => 'test']);
        
        SystemSetting::set('app_installed', true);
        SystemSetting::set('services_fx', true);
        SystemSetting::set('fx_fee_percent', 1.5);
        // Set fallback in system settings
        SystemSetting::set('fx_rate_usd', 1500.00);

        // Fake the exchange rate API to fail
        Http::fake([
            'open.er-api.com/*' => Http::response(null, 500)
        ]);

        $response = $this->actingAs($user)->postJson('/services/fx/exchange', [
            'from_currency' => 'USD',
            'to_currency' => 'NGN',
            'amount' => 100
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('status', true)
                 ->assertJsonPath('rate', 1500);
    }
}
