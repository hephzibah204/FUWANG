<?php

namespace Tests\Feature;

use App\Models\AccountBalance;
use App\Models\Admin;
use App\Models\ApiToken;
use App\Models\CustomApi;
use App\Models\SystemSetting;
use App\Models\Transaction;
use App\Models\User;
use App\Services\DeveloperApi\DeveloperApiCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DeveloperApiApplicationAndCustomPricingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        SystemSetting::clearLocalCache();
        Cache::flush();
        app(DeveloperApiCatalog::class)->ensureDefaults();
    }

    private function makeUser(string $email = 'dev@example.com', string $status = 'none', float $balance = 5000.0): User
    {
        $user = User::create([
            'fullname' => 'Test Developer',
            'username' => 'dev_' . substr(md5($email), 0, 8),
            'email' => $email,
            'password' => Hash::make('Password@123'),
            'api_access_status' => $status,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        AccountBalance::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'user_balance' => $balance,
            'api_key' => 'user',
        ]);

        return $user;
    }

    private function makeToken(User $user): array
    {
        $plain = 'plain-' . substr(md5($user->email . microtime()), 0, 12);
        $token = ApiToken::create([
            'user_id' => $user->id,
            'name' => 'Test Key',
            'token_hash' => hash('sha256', $plain),
            'last_four' => substr($plain, -4),
            'abilities' => ['*'],
            'rate_limit_per_minute' => 60,
        ]);

        return [$token, 'nx_' . $plain];
    }

    public function test_unapproved_user_cannot_generate_api_token(): void
    {
        $user = $this->makeUser('unapproved@example.com', 'none');

        $response = $this->actingAs($user)->postJson(route('developer.tokens.create'), [
            'name' => 'Attempted Key',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('status', false)
            ->assertJsonPath('error', 'api_access_not_approved');

        $this->assertDatabaseMissing('api_tokens', [
            'user_id' => $user->id,
        ]);
    }

    public function test_developer_can_submit_api_application_with_website_and_purpose(): void
    {
        $user = $this->makeUser('applicant@example.com', 'none');

        $response = $this->actingAs($user)->postJson(route('developer.apply'), [
            'website' => 'https://fintechapp.ng',
            'intended_use' => 'Customer identity verification and KYC onboarding for fintech wallet users.',
            'company_name' => 'FintechApp Global Ltd',
            'estimated_monthly_volume' => '10,000 verifications',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('access_status', 'pending');

        $user->refresh();
        $this->assertSame('pending', $user->api_access_status);
        $this->assertSame('https://fintechapp.ng', data_get($user->api_application_details, 'website'));
        $this->assertSame('Customer identity verification and KYC onboarding for fintech wallet users.', data_get($user->api_application_details, 'intended_use'));
        $this->assertSame('FintechApp Global Ltd', data_get($user->api_application_details, 'company_name'));
        $this->assertSame('10,000 verifications', data_get($user->api_application_details, 'estimated_monthly_volume'));
        $this->assertNotNull(data_get($user->api_application_details, 'applied_at'));
    }

    public function test_approved_developer_can_generate_api_token(): void
    {
        $user = $this->makeUser('approved@example.com', 'approved');

        $response = $this->actingAs($user)->postJson(route('developer.tokens.create'), [
            'name' => 'Production Key',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonStructure(['token', 'token_id']);

        $this->assertDatabaseHas('api_tokens', [
            'user_id' => $user->id,
            'name' => 'Production Key',
        ]);
    }

    public function test_admin_can_set_custom_pricing_for_specific_developer(): void
    {
        $admin = Admin::create([
            'name' => 'Admin User',
            'username' => 'admin_vip',
            'email' => 'admin@example.com',
            'password' => Hash::make('AdminPass@123'),
            'role' => 'admin',
        ]);

        $developer = $this->makeUser('vip-dev@example.com', 'approved');

        $this->actingAs($admin, 'admin')->post(route('admin.developer_api.user_pricing', $developer->id), [
            'developer_api_nin_price' => '65.50',
            'developer_api_bvn_basic_price' => '80.00',
            'developer_api_bvn_premium_price' => '400.00',
        ])->assertSessionHas('success');

        $developer->refresh();
        $this->assertTrue($developer->hasCustomApiPricing());
        $this->assertSame(65.5, $developer->getDeveloperApiPrice('developer_api_nin_price', 100.0));
        $this->assertSame(80.0, $developer->getDeveloperApiPrice('developer_api_bvn_basic_price', 100.0));
        $this->assertSame(400.0, $developer->getDeveloperApiPrice('developer_api_bvn_premium_price', 500.0));
        // CAC was not set, so falls back to system default 300
        $this->assertSame(300.0, $developer->getDeveloperApiPrice('developer_api_cac_price', 300.0));
    }

    public function test_custom_priced_developer_is_charged_custom_rate_on_nin_verification(): void
    {
        $developer = $this->makeUser('custom-nin@example.com', 'approved', 2000.0);
        $developer->api_custom_pricing = [
            'developer_api_nin_price' => 70.0, // Custom rate: ₦70.00 instead of default ₦100.00
        ];
        $developer->save();

        [$token, $bearer] = $this->makeToken($developer);

        CustomApi::create([
            'name' => 'Mock NIN Gateway',
            'service_type' => 'nin_verification',
            'endpoint' => 'https://gateway.example.com/nin',
            'status' => true,
            'priority' => 1,
            'price' => 200, // Provider raw cost is higher, but developer pays user custom price
        ]);

        Http::fake([
            'https://gateway.example.com/*' => Http::response([
                'status' => 'success',
                'data' => [
                    'nin' => '12345678901',
                    'fullname' => 'Test User',
                ],
            ], 200),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $bearer)
            ->postJson('/api/v1/verifications/nin', [
                'number' => '12345678901',
                'firstname' => 'Test',
                'lastname' => 'User',
                'dob' => '1990-01-01',
                'mode' => 'nin',
            ]);

        $response->assertOk()->assertJsonPath('status', true);

        $tx = Transaction::query()
            ->where('user_email', $developer->email)
            ->where('order_type', 'API: NIN Verification')
            ->latest()
            ->first();

        $this->assertNotNull($tx);
        $this->assertSame(2000.0, (float) $tx->balance_before);
        $this->assertSame(1930.0, (float) $tx->balance_after); // 2000 - 70 = 1930
        $this->assertSame(70.0, (float) ($tx->balance_before - $tx->balance_after));
    }

    public function test_standard_developer_without_custom_price_is_charged_default_rate(): void
    {
        $developer = $this->makeUser('standard-dev@example.com', 'approved', 2000.0);
        [$token, $bearer] = $this->makeToken($developer);

        CustomApi::create([
            'name' => 'Mock NIN Gateway',
            'service_type' => 'nin_verification',
            'endpoint' => 'https://gateway.example.com/nin',
            'status' => true,
            'priority' => 1,
            'price' => 200,
        ]);

        Http::fake([
            'https://gateway.example.com/*' => Http::response([
                'status' => 'success',
                'data' => [
                    'nin' => '98765432109',
                    'fullname' => 'Standard User',
                ],
            ], 200),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $bearer)
            ->postJson('/api/v1/verifications/nin', [
                'number' => '98765432109',
                'firstname' => 'Standard',
                'lastname' => 'User',
                'dob' => '1992-02-02',
                'mode' => 'nin',
            ]);

        $response->assertOk()->assertJsonPath('status', true);

        $tx = Transaction::query()
            ->where('user_email', $developer->email)
            ->where('order_type', 'API: NIN Verification')
            ->latest()
            ->first();

        $this->assertNotNull($tx);
        $this->assertSame(2000.0, (float) $tx->balance_before);
        $this->assertSame(1900.0, (float) $tx->balance_after); // 2000 - 100 = 1900
        $this->assertSame(100.0, (float) ($tx->balance_before - $tx->balance_after));
    }

    public function test_developer_portal_displays_custom_price_for_custom_priced_user(): void
    {
        $developer = $this->makeUser('custom-ui@example.com', 'approved');
        $developer->api_custom_pricing = [
            'developer_api_nin_price' => 77.0,
        ];
        $developer->save();

        $this->actingAs($developer)
            ->get('/developer')
            ->assertOk()
            ->assertSee('₦77.00')
            ->assertSee('Custom Pricing Active');

        $this->actingAs($developer)
            ->get(route('developer.docs'))
            ->assertOk()
            ->assertSee('₦77.00');
    }

    public function test_admin_can_reset_custom_pricing_back_to_defaults(): void
    {
        $admin = Admin::create([
            'name' => 'Admin Reset',
            'username' => 'admin_reset',
            'email' => 'admin-reset@example.com',
            'password' => Hash::make('AdminPass@123'),
            'role' => 'admin',
        ]);

        $developer = $this->makeUser('reset-dev@example.com', 'approved');
        $developer->api_custom_pricing = [
            'developer_api_nin_price' => 50.0,
        ];
        $developer->save();

        $this->assertTrue($developer->hasCustomApiPricing());

        $this->actingAs($admin, 'admin')->post(route('admin.developer_api.user_pricing', $developer->id), [
            'reset_to_default' => '1',
        ])->assertSessionHas('success');

        $developer->refresh();
        $this->assertFalse($developer->hasCustomApiPricing());
        $this->assertNull($developer->api_custom_pricing);
        $this->assertSame(100.0, $developer->getDeveloperApiPrice('developer_api_nin_price', 100.0));
    }

    public function test_admin_can_update_global_pricing_for_all_apis_including_ipe_and_vtu(): void
    {
        $admin = Admin::create([
            'name' => 'Admin Super',
            'username' => 'admin_super',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('AdminPass@123'),
            'role' => 'admin',
        ]);

        $this->actingAs($admin, 'admin')->post(route('admin.developer_api.pricing'), [
            'developer_api_nin_price' => '110.00',
            'developer_api_nin_validation_price' => '450.00',
            'developer_api_ipe_clearance_price' => '650.00',
            'developer_api_bvn_basic_price' => '120.00',
            'developer_api_bvn_premium_price' => '550.00',
            'developer_api_cac_price' => '320.00',
            'developer_api_drivers_license_price' => '210.00',
            'developer_api_tin_price' => '190.00',
            'developer_api_voters_card_price' => '140.00',
            'developer_api_vtu_airtime_discount' => '2.5',
            'developer_api_vtu_data_discount' => '4.0',
            'developer_api_vtu_cable_fee' => '10.00',
            'developer_api_vtu_electricity_fee' => '15.00',
        ])->assertSessionHas('success');

        $this->assertSame(450.0, (float) SystemSetting::get('developer_api_nin_validation_price'));
        $this->assertSame(650.0, (float) SystemSetting::get('developer_api_ipe_clearance_price'));
        $this->assertSame(2.5, (float) SystemSetting::get('developer_api_vtu_airtime_discount'));
        $this->assertSame(4.0, (float) SystemSetting::get('developer_api_vtu_data_discount'));
    }

    public function test_custom_priced_developer_is_charged_custom_rate_on_ipe_clearance_api(): void
    {
        $developer = $this->makeUser('dev-ipe@example.com', 'approved', 2000.0);
        $developer->api_custom_pricing = [
            'developer_api_ipe_clearance_price' => 550.0, // Custom rate: ₦550 instead of ₦700
        ];
        $developer->save();

        [$token, $bearer] = $this->makeToken($developer);

        CustomApi::create([
            'name' => 'DataVerify Clearance',
            'service_type' => 'ipe_clearance',
            'provider_identifier' => 'dataverify',
            'endpoint' => 'https://dataverify.org/api/developers/ipe.php',
            'api_key' => 'mock-dv-key',
            'status' => true,
            'priority' => 1,
        ]);

        Http::fake([
            'https://dataverify.org/*' => Http::response([
                'status' => 'successful',
                'message' => 'Tracking cleared',
                'data' => [
                    'tracking_id' => '123456789012345',
                    'new_tracking_id' => '987654321098765',
                ],
            ], 200),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $bearer)
            ->postJson('/api/v1/verifications/clearance', [
                'tracking_id' => '123456789012345',
                'category' => 'Improcessing Error',
            ]);

        $response->assertOk()->assertJsonPath('status', true);

        $developer->refresh();
        $this->assertSame(1450.0, (float) $developer->balance->user_balance); // 2000 - 550 = 1450
    }

    public function test_custom_priced_developer_is_charged_custom_rate_on_nin_validation_api(): void
    {
        $developer = $this->makeUser('dev-val@example.com', 'approved', 2000.0);
        $developer->api_custom_pricing = [
            'developer_api_nin_validation_price' => 420.0, // Custom rate: ₦420 instead of ₦500
        ];
        $developer->save();

        [$token, $bearer] = $this->makeToken($developer);

        $response = $this->withHeader('Authorization', 'Bearer ' . $bearer)
            ->postJson('/api/v1/verifications/validation', [
                'number' => '12345678901',
                'validation_reason' => 'Sim Validation',
            ]);

        $response->assertOk()->assertJsonPath('status', true);

        $developer->refresh();
        $this->assertSame(1580.0, (float) $developer->balance->user_balance); // 2000 - 420 = 1580
    }

    public function test_developer_receives_configured_discount_on_vtu_airtime_api(): void
    {
        $developer = $this->makeUser('dev-vtu@example.com', 'approved', 3000.0);
        $developer->api_custom_pricing = [
            'developer_api_vtu_airtime_discount' => 5.0, // Custom VIP 5% discount
        ];
        $developer->save();

        [$token, $bearer] = $this->makeToken($developer);

        CustomApi::create([
            'name' => 'VTU Gateway',
            'service_type' => 'vtu_airtime',
            'endpoint' => 'https://vtu-gateway.example.com/airtime',
            'api_key' => 'mock-api-key',
            'status' => true,
            'priority' => 1,
        ]);

        Http::fake([
            'https://vtu-gateway.example.com/*' => Http::response([
                'status' => 'success',
                'message' => 'Airtime topup successful',
                'reference' => 'AIR-TX-9988',
            ], 200),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $bearer)
            ->postJson('/api/v1/vtu/airtime', [
                'network' => 'MTN',
                'amount' => 1000,
                'phone' => '08012345678',
            ]);

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('breakdown.amount', 1000)
            ->assertJsonPath('breakdown.discount', 50)
            ->assertJsonPath('breakdown.total', 950);

        $developer->refresh();
        // 3000 - 950 = 2050
        $this->assertSame(2050.0, (float) $developer->balance->user_balance);
    }
}
