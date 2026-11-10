<?php

namespace Tests\Feature;

use App\Models\AccountBalance;
use App\Models\ApiCenter;
use App\Models\CustomApi;
use App\Models\FeatureToggle;
use App\Models\User;
use App\Models\VerificationPrice;
use App\Models\VerificationResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PersonalizationSmartSwitchAndBandsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected CustomApi $robostApi;
    protected CustomApi $dvApi;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        FeatureToggle::query()->updateOrCreate(
            ['feature_name' => 'identity_verification'],
            ['is_active' => true]
        );

        $this->user = User::create([
            'fullname' => 'Personalization Client',
            'username' => 'pers_client_' . uniqid(),
            'email' => 'pers_client@example.com',
            'password' => Hash::make('Password@123'),
        ]);
        $this->user->forceFill(['email_verified_at' => now()])->save();

        AccountBalance::create([
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'user_balance' => 50000.00,
            'api_key' => 'user',
        ]);

        VerificationPrice::create([
            'nin_by_nin_price' => 200,
            'validation_price' => 500,
            'ipe_clearance_price' => 700,
            'personalization_price' => 100,
        ]);

        ApiCenter::create([
            'robosttech_api_key' => 'robost-key-test',
            'robosttech_endpoint_personalization' => 'https://robosttech.com/api/personalization',
            'dataverify_api_key' => 'dv-key-test',
            'dataverify_endpoint_personalization' => 'https://dataverify.org/api/developers/personalization.php',
            'dataverify_endpoint_personalization_status' => 'https://dataverify.org/api/developers/personalization_status.php',
        ]);

        $this->robostApi = CustomApi::create([
            'name' => 'RobostTech API',
            'service_type' => 'personalization',
            'provider_identifier' => 'robosttech',
            'endpoint' => 'https://robosttech.com/api/personalization',
            'api_key' => 'robost-key-test',
            'status' => true,
            'priority' => 1,
            'supported_modes' => ['personalization', 'personalization_status'],
        ]);

        $this->dvApi = CustomApi::create([
            'name' => 'DataVerify API',
            'service_type' => 'personalization',
            'provider_identifier' => 'dataverify',
            'endpoint' => 'https://dataverify.org/api/developers/personalization.php',
            'api_key' => 'dv-key-test',
            'status' => true,
            'priority' => 2,
            'supported_modes' => ['personalization', 'personalization_status'],
        ]);
    }

    public function test_customer_can_choose_band_1_for_personalization(): void
    {
        $robostCalled = false;
        $dvCalled = false;

        Http::fake([
            'https://robosttech.com/api/personalization' => function ($request) use (&$robostCalled) {
                $robostCalled = true;
                $this->assertEquals('robost-key-test', $request->header('api-key')[0] ?? null);
                $this->assertEquals('TRK-BAND1-001', $request->data()['tracking_id'] ?? null);

                return Http::response([
                    'success' => true,
                    'approved' => true,
                    'message' => 'Personalization Request Added Successfully',
                ], 200);
            },
            'https://dataverify.org/*' => function ($request) use (&$dvCalled) {
                $dvCalled = true;
                return Http::response(['status' => false], 400);
            },
        ]);

        $response = $this->actingAs($this->user)->postJson(route('services.personalization.verify'), [
            'tracking_id' => 'TRK-BAND1-001',
            'network_band' => 'band_1',
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', true);
        $response->assertJsonPath('is_pending', true);
        $this->assertTrue($robostCalled, 'Band 1 should invoke RobostTech');
        $this->assertFalse($dvCalled, 'DataVerify should not be invoked when Band 1 is selected');

        $this->assertDatabaseHas('verification_results', [
            'identifier' => 'TRK-BAND1-001',
            'provider_name' => 'Robosttech',
            'status' => 'pending',
        ]);
    }

    public function test_customer_can_choose_band_2_for_personalization(): void
    {
        $robostCalled = false;
        $dvCalled = false;

        Http::fake([
            'https://dataverify.org/api/developers/personalization.php' => function ($request) use (&$dvCalled) {
                $dvCalled = true;
                $data = $request->data();
                $this->assertEquals('dv-key-test', $data['api_key'] ?? null);
                $this->assertEquals('TRK-BAND2-002', $data['tracking_id'] ?? null);

                return Http::response([
                    'status' => true,
                    'accepted' => true,
                    'category' => 'to_get_slip',
                    'transaction_id' => 'DV-TX-PERS-9988',
                    'price' => 100,
                    'balance_before' => 1000,
                    'balance_after' => 900,
                    'request_status' => 'pending',
                    'message' => 'Ticket accepted for processing',
                ], 200);
            },
            'https://robosttech.com/*' => function ($request) use (&$robostCalled) {
                $robostCalled = true;
                return Http::response(['status' => false], 400);
            },
        ]);

        $response = $this->actingAs($this->user)->postJson(route('services.personalization.verify'), [
            'tracking_id' => 'TRK-BAND2-002',
            'network_band' => 'band_2',
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', true);
        $response->assertJsonPath('is_pending', true);
        $this->assertTrue($dvCalled, 'Band 2 should invoke DataVerify');
        $this->assertFalse($robostCalled, 'RobostTech should not be invoked when Band 2 is selected');

        $this->assertDatabaseHas('verification_results', [
            'identifier' => 'TRK-BAND2-002',
            'provider_name' => 'DataVerify',
            'status' => 'pending',
        ]);
    }

    public function test_smartswitch_auto_fails_over_from_band_1_to_band_2(): void
    {
        $robostCalled = false;
        $dvCalled = false;

        Http::fake([
            'https://robosttech.com/api/personalization' => function ($request) use (&$robostCalled) {
                $robostCalled = true;
                return Http::response([
                    'status' => 'failed',
                    'message' => 'Provider gateway 502 Bad Gateway',
                ], 502);
            },
            'https://dataverify.org/api/developers/personalization.php' => function ($request) use (&$dvCalled) {
                $dvCalled = true;
                return Http::response([
                    'status' => true,
                    'accepted' => true,
                    'category' => 'to_get_slip',
                    'transaction_id' => 'DV-TX-FAILOVER-01',
                    'price' => 100,
                    'request_status' => 'pending',
                    'message' => 'Accepted by DataVerify fallback',
                ], 200);
            },
        ]);

        // Default 'auto' mode triggers SmartSwitch
        $response = $this->actingAs($this->user)->postJson(route('services.personalization.verify'), [
            'tracking_id' => 'TRK-SW-FAILOVER-01',
            'network_band' => 'auto',
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', true);
        $this->assertTrue($robostCalled, 'SmartSwitch should attempt Band 1 first');
        $this->assertTrue($dvCalled, 'SmartSwitch should failover to Band 2 when Band 1 fails');

        $this->assertDatabaseHas('verification_results', [
            'identifier' => 'TRK-SW-FAILOVER-01',
            'provider_name' => 'DataVerify',
            'status' => 'pending',
        ]);
    }

    public function test_status_polling_for_band_2_dataverify_personalization(): void
    {
        $dummyPdf = base64_encode('%PDF-1.4 test dummy pdf content');

        Http::fake([
            'https://dataverify.org/api/developers/personalization_status.php' => function ($request) {
                return Http::response([
                    'status' => true,
                    'request_status' => 'completed',
                    'nin' => '99887766554',
                    'user_data' => [
                        'firstname' => 'FATIMA',
                        'lastname' => 'MOHAMMED',
                        'nin' => '99887766554',
                    ],
                    'pdf_base64' => base64_encode('%PDF-1.4 test dummy pdf content'),
                    'message' => 'Personalization successfully completed',
                ], 200);
            },
        ]);

        $result = VerificationResult::create([
            'user_id' => $this->user->id,
            'service_type' => 'personalization',
            'identifier' => 'TRK-POLL-DV-01',
            'provider_name' => 'DataVerify',
            'status' => 'pending',
            'reference_id' => 'PERS-REF-DV-01',
            'response_data' => [
                'tracking_id' => 'TRK-POLL-DV-01',
                'network_band' => 'Band 2',
                'provider_name' => 'DataVerify',
                'transaction_id' => 'DV-TX-999',
            ],
        ]);

        $response = $this->actingAs($this->user)->postJson(route('services.personalization.verify'), [
            'mode' => 'status',
            'tracking_id' => 'TRK-POLL-DV-01',
            'result_id' => $result->id,
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', true);
        $response->assertJsonPath('is_completed', true);
        $response->assertJsonPath('nin', '99887766554');

        $result->refresh();
        $this->assertEquals('successful', $result->status);
        $this->assertEquals('99887766554', $result->response_data['nin'] ?? null);
    }
}
