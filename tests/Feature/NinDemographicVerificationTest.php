<?php

namespace Tests\Feature;

use App\Models\AccountBalance;
use App\Models\ApiCenter;
use App\Models\ApiToken;
use App\Models\CustomApi;
use App\Models\FeatureToggle;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\VerificationPrice;
use App\Models\VerificationResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NinDemographicVerificationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private CustomApi $robostApi;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        FeatureToggle::query()->updateOrCreate(
            ['feature_name' => 'identity_verification'],
            ['is_active' => true]
        );

        $this->user = User::create([
            'fullname' => 'Demographic Test User',
            'username' => 'demo_tester',
            'email' => 'demographic.user@example.com',
            'password' => Hash::make('Secret123@!'),
            'api_key' => 'user_key_demo_token_123',
        ]);
        $this->user->forceFill(['email_verified_at' => now()])->save();

        AccountBalance::create([
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'user_balance' => 15000.00,
            'api_key' => 'user_key_demo_token_123',
        ]);

        VerificationPrice::updateOrCreate(['id' => 1], [
            'nin_by_nin_price' => 200.00,
            'nin_by_phone_price' => 250.00,
            'validation_price' => 700.00,
        ]);

        ApiCenter::create([
            'robosttech_api_key' => 'test_robost_api_key_demo_123',
            'robosttech_endpoint_nin' => 'https://robosttech.com/api',
            'default_nin_provider' => 'robosttech',
        ]);

        $this->robostApi = CustomApi::create([
            'name' => 'RobostTech Demo API',
            'service_type' => 'nin_verification',
            'provider_identifier' => 'robosttech',
            'endpoint' => 'https://robosttech.com/api/nin_verify',
            'api_key' => 'test_robost_api_key_demo_123',
            'status' => true,
            'priority' => 5,
            'supported_modes' => ['nin', 'phone', 'demographic', 'validation', 'validation_status'],
        ]);
    }

    public function test_web_ui_demographic_search_verifies_with_robosttech_api_spec(): void
    {
        Http::fake([
            'https://robosttech.com/api/nin_demo' => function ($request) {
                $this->assertEquals('application/json', $request->header('Content-Type')[0] ?? '');
                $this->assertEquals('application/json', $request->header('Accept')[0] ?? '');
                $this->assertEquals('test_robost_api_key_demo_123', $request->header('api-key')[0] ?? '');

                $payload = $request->data();
                $this->assertEquals('WASILA', $payload['firstname']);
                $this->assertEquals('BALA', $payload['lastname']);
                $this->assertEquals('AISHA', $payload['middlename']);
                $this->assertEquals('female', $payload['gender']);
                $this->assertEquals('2009-01-01', $payload['dateOfBirth']);

                return Http::response([
                    'status' => true,
                    'message' => 'Verification Successfull',
                    'data' => [
                        'nin' => '13099075002',
                        'firstname' => 'WASILA',
                        'surname' => 'BALA',
                        'middlename' => 'AISHA',
                        'birthdate' => '2009-01-01',
                        'gender' => 'female',
                        'photo' => 'data:image/jpeg;base64,mockphoto',
                    ],
                ], 200);
            },
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('services.nin.verify'), [
                'mode' => 'demographic',
                'firstname' => 'wasila',
                'lastname' => 'bala',
                'middlename' => 'aisha',
                'dob' => '2009-01-01',
                'gender' => 'female',
                'output_type' => 'premium_slip',
                'api_provider_id' => $this->robostApi->id,
            ]);

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.nin', '13099075002')
            ->assertJsonPath('data.firstname', 'WASILA');

        // Check slip_url generation
        $slipUrl = $response->json('slip_url');
        $this->assertNotEmpty($slipUrl);
        $this->assertStringContainsString('premium_slip', $slipUrl);

        // VerificationResult created with nin as identifier
        $result = VerificationResult::latest()->first();
        $this->assertNotNull($result);
        $this->assertEquals('13099075002', $result->identifier);
        $this->assertEquals('RobostTech Demo API', $result->getRawOriginal('provider_name'));
        $this->assertEquals('success', $result->status);
    }

    public function test_web_ui_demographic_search_normalizes_dd_mm_yyyy_date_and_gender(): void
    {
        Http::fake([
            'https://robosttech.com/api/nin_demo' => function ($request) {
                $payload = $request->data();
                $this->assertEquals('2005-12-25', $payload['dateOfBirth']);
                $this->assertEquals('male', $payload['gender']);

                return Http::response([
                    'status' => 'success',
                    'message' => 'Verification Successfull',
                    'data' => [
                        'nin' => '99887766554',
                        'firstname' => 'JOHN',
                        'surname' => 'DOE',
                    ],
                ], 200);
            },
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('services.nin.verify'), [
                'mode' => 'demographic',
                'firstname' => 'JOHN',
                'lastname' => 'DOE',
                'dob' => '25-12-2005', // DD-MM-YYYY normalized to 2005-12-25
                'gender' => 'M', // 'M' normalized to 'male'
                'api_provider_id' => $this->robostApi->id,
            ]);

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.nin', '99887766554');
    }

    public function test_developer_api_demographic_search_without_number_parameter(): void
    {
        $plainToken = 'api_token_demo_987654';
        $this->user->update(['api_access_status' => 'approved']);
        ApiToken::create([
            'user_id' => $this->user->id,
            'name' => 'Demo Test Token',
            'token_hash' => hash('sha256', $plainToken),
        ]);

        Http::fake([
            'https://robosttech.com/api/nin_demo' => function ($request) {
                $payload = $request->data();
                $this->assertEquals('WASILA', $payload['firstname']);
                $this->assertEquals('BALA', $payload['lastname']);
                $this->assertEquals('2009-01-01', $payload['dateOfBirth']);
                $this->assertEquals('female', $payload['gender']);

                return Http::response([
                    'status' => true,
                    'message' => 'Verification Successfull',
                    'data' => [
                        'nin' => '13099075002',
                        'firstname' => 'WASILA',
                        'surname' => 'BALA',
                    ],
                ], 200);
            },
        ]);

        $response = $this->postJson('/api/v1/verifications/nin', [
            'mode' => 'demographic',
            'firstname' => 'WASILA',
            'lastname' => 'BALA',
            'dateOfBirth' => '2009-01-01',
            'gender' => 'female',
            'api_provider_id' => $this->robostApi->id,
        ], [
            'Authorization' => 'Bearer ' . $plainToken,
        ]);

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.nin', '13099075002');
    }
}
