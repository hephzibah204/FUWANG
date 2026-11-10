<?php

namespace Tests\Feature;

use App\Models\AccountBalance;
use App\Models\ApiCenter;
use App\Models\CustomApi;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\VerificationPrice;
use App\Models\VerificationResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RobosttechFirstChoiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected CustomApi $robostApi;
    protected CustomApi $dvApi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'fullname' => 'Test Client',
            'username' => 'client_' . uniqid(),
            'email' => 'client@fuwa.test',
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
            'nin_by_number_price' => 200,
            'verify_by_tracking_id' => 300,
            'validation_price' => 500,
            'ipe_clearance_price' => 700,
        ]);

        // Configure active API credentials for both Robosttech and DataVerify
        ApiCenter::create([
            'robosttech_api_key' => 'robost-secret-token-test',
            'robosttech_endpoint_validation' => 'https://robosttech.com/api/validation',
            'robosttech_endpoint_validation_status' => 'https://robosttech.com/api/validation_status',
            'robosttech_endpoint_clearance' => 'https://robosttech.com/api/clearance',
            'robosttech_endpoint_clearance_status' => 'https://robosttech.com/api/clearance_status',
            'dataverify_api_key' => 'dv-secret-token-test',
        ]);

        $this->robostApi = CustomApi::create([
            'name' => 'RobostTech API',
            'service_type' => 'nin_verification',
            'provider_identifier' => 'robosttech',
            'endpoint' => 'https://robosttech.com/api/nin_verify',
            'api_key' => 'robost-secret-token-test',
            'status' => true,
            'priority' => 5,
            'supported_modes' => ['nin', 'phone', 'demographic', 'validation', 'validation_status', 'clearance', 'clearance_status'],
        ]);

        $this->dvApi = CustomApi::create([
            'name' => 'DataVerify API',
            'service_type' => 'nin_verification',
            'provider_identifier' => 'dataverify',
            'endpoint' => 'https://dataverify.org/developers/nin_slips/nin_premium',
            'api_key' => 'dataverify57b5ef07cb66caaadee990a6aa733ede',
            'status' => true,
            'priority' => 10,
            'supported_modes' => ['nin', 'phone', 'demographic', 'tracking', 'validation', 'validation_status'],
        ]);
    }

    public function test_phone_search_selects_robosttech_as_first_choice(): void
    {
        $robostPhoneCalled = false;
        $dvCalled = false;

        Http::fake([
            'https://robosttech.com/api/nin_phone' => function ($request) use (&$robostPhoneCalled) {
                $robostPhoneCalled = true;
                $this->assertEquals('robost-secret-token-test', $request->header('api-key')[0] ?? null);
                $this->assertEquals('08032364684', $request->data()['phone'] ?? null);

                return Http::response([
                    'success' => true,
                    'status' => 'success',
                    'data' => [
                        'firstname' => 'FIRSTNAME_PHONE',
                        'surname' => 'SURNAME_PHONE',
                        'nin' => '12345678901',
                        'phone' => '08032364684',
                    ]
                ], 200);
            },
            'https://dataverify.org/*' => function ($request) use (&$dvCalled) {
                $dvCalled = true;
                return Http::response(['status' => false], 400);
            },
        ]);

        $response = $this->actingAs($this->user)->post(route('services.nin.verify'), [
            'mode' => 'phone',
            'number' => '08032364684',
            // No api_provider_id sent: system should auto-pick Robosttech as first choice
        ]);

        $this->assertTrue($robostPhoneCalled, 'Robosttech phone endpoint should have been called as first choice');
        $this->assertFalse($dvCalled, 'DataVerify should not have been called when Robosttech succeeded');

        $this->assertDatabaseHas('verification_results', [
            'service_type' => 'nin_verification',
            'provider_name' => 'RobostTech API',
            'identifier' => '08032364684',
        ]);
    }

    public function test_nin_validation_selects_robosttech_as_first_choice_in_auto_mode(): void
    {
        SystemSetting::set('nin_validation_mode', 'auto');

        $robostValCalled = false;
        $dvCalled = false;

        Http::fake([
            'https://robosttech.com/api/validation' => function ($request) use (&$robostValCalled) {
                $robostValCalled = true;
                $this->assertEquals('12345678901', $request->data()['nin'] ?? null);

                return Http::response([
                    'success' => true,
                    'approved' => true,
                    'message' => 'Validation submitted to Robosttech queue',
                ], 200);
            },
            'https://dataverify.org/*' => function ($request) use (&$dvCalled) {
                $dvCalled = true;
                return Http::response(['status' => false], 400);
            },
        ]);

        $response = $this->actingAs($this->user)->postJson(route('services.validation.verify'), [
            'mode' => 'submit',
            'number' => '12345678901',
            'validation_reason' => 'SIM Validation',
        ]);

        $response->assertOk();
        $this->assertTrue($robostValCalled, 'Robosttech validation should have been invoked first');
        $this->assertFalse($dvCalled, 'DataVerify should not be invoked when Robosttech succeeds');

        $this->assertDatabaseHas('verification_results', [
            'identifier' => '12345678901',
            'provider_name' => 'RobostTech API',
            'status' => 'waiting_for_review',
        ]);
    }

    public function test_ipe_clearance_selects_robosttech_as_first_choice_in_auto_mode(): void
    {
        SystemSetting::set('ipe_clearance_mode', 'auto');

        $robostClrCalled = false;
        $dvCalled = false;

        Http::fake([
            'https://robosttech.com/api/clearance' => function ($request) use (&$robostClrCalled) {
                $robostClrCalled = true;
                $this->assertEquals('BTX-CLEAR-ROBOST-01', $request->data()['tracking_id'] ?? null);

                return Http::response([
                    'success' => true,
                    'message' => 'IPE clearance registered with Robosttech',
                ], 200);
            },
            'https://dataverify.org/*' => function ($request) use (&$dvCalled) {
                $dvCalled = true;
                return Http::response(['status' => false], 400);
            },
        ]);

        $response = $this->actingAs($this->user)->postJson(route('services.clearance.verify'), [
            'mode' => 'submit',
            'number' => 'BTX-CLEAR-ROBOST-01',
            'category' => 'Improcessing Error',
        ]);

        $response->assertOk();
        $this->assertTrue($robostClrCalled, 'Robosttech clearance should have been called first');
        $this->assertFalse($dvCalled, 'DataVerify clearance should not be called when Robosttech succeeds');

        $this->assertDatabaseHas('verification_results', [
            'identifier' => 'BTX-CLEAR-ROBOST-01',
            'status' => 'waiting_for_review',
        ]);
    }

    public function test_developer_api_phone_verification_uses_robosttech_first(): void
    {
        $robostPhoneCalled = false;

        Http::fake([
            'https://robosttech.com/api/nin_phone' => function ($request) use (&$robostPhoneCalled) {
                $robostPhoneCalled = true;
                return Http::response([
                    'success' => true,
                    'data' => [
                        'firstname' => 'API_WASILA',
                        'surname' => 'API_BALA',
                        'nin' => '12345678901',
                        'phone' => '08032364684',
                    ]
                ], 200);
            },
        ]);

        $plainToken = 'robost_auto_first_choice_token';
        $this->user->update(['api_access_status' => 'approved']);
        \App\Models\ApiToken::create([
            'user_id' => $this->user->id,
            'name' => 'API Choice Token',
            'token_hash' => hash('sha256', $plainToken),
        ]);

        $response = $this->postJson('/api/v1/verifications/nin', [
            'mode' => 'phone',
            'number' => '08032364684',
            // No api_provider_id sent
        ], [
            'Authorization' => 'Bearer ' . $plainToken,
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', true);
        $response->assertJsonPath('data.firstname', 'API_WASILA');
        $this->assertTrue($robostPhoneCalled, 'Robosttech was selected as first choice by Developer API');
    }
}
