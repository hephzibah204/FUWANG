<?php

namespace Tests\Feature;

use App\Models\AccountBalance;
use App\Models\ApiCenter;
use App\Models\CustomApi;
use App\Models\CustomApiVerificationType;
use App\Models\FeatureToggle;
use App\Models\User;
use App\Models\VerificationResult;
use App\Services\DataVerify\DataVerifyClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RobosttechAndDataverifyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        FeatureToggle::query()->updateOrCreate(
            ['feature_name' => 'identity_verification'],
            ['is_active' => true]
        );
    }

    public function test_dataverify_client_verifies_nin_successfully(): void
    {
        Http::fake([
            'https://dataverify.org/*' => Http::response([
                'status' => 'success',
                'data' => [
                    'nin' => '11223344556',
                    'firstname' => 'Musa',
                    'lastname' => 'Ibrahim',
                    'gender' => 'male',
                    'birthdate' => '1995-05-15',
                ],
            ], 200),
        ]);

        $customApi = CustomApi::create([
            'name' => 'DataVerify Test',
            'provider_identifier' => 'dataverify',
            'service_type' => 'nin_verification',
            'endpoint' => 'https://dataverify.org/developers/nin_api/',
            'api_key' => 'dv_test_key_123',
            'status' => true,
        ]);

        $client = new DataVerifyClient($customApi);
        $result = $client->verify('nin', ['number' => '11223344556']);

        $this->assertTrue($result['ok']);
        $this->assertSame('11223344556', $result['data']['nin']);
        $this->assertSame('Musa', $result['data']['firstname']);
    }

    public function test_dataverify_client_checks_balance_successfully(): void
    {
        Http::fake([
            'https://dataverify.org/api/developers/balance.php*' => Http::response([
                'status' => 'success',
                'balance' => '50000.00',
            ], 200),
        ]);

        $customApi = CustomApi::create([
            'name' => 'DataVerify Test',
            'provider_identifier' => 'dataverify',
            'service_type' => 'nin_verification',
            'endpoint' => 'https://dataverify.org/developers/nin_api/',
            'api_key' => 'dv_test_key_123',
            'status' => true,
        ]);

        $client = new DataVerifyClient($customApi);
        $balance = $client->checkBalance();

        $this->assertTrue($balance['ok']);
        $this->assertSame(50000.0, (float) $balance['balance']);
    }

    public function test_dataverify_client_verifies_tracking_by_tid_without_nin(): void
    {
        Http::fake([
            'https://dataverify.org/developers/nin_api/fetch_by_tid*' => function (\Illuminate\Http\Client\Request $request) {
                $data = $request->data();
                $this->assertEquals('BTX947E60001020', $data['tracking_id']);
                $this->assertArrayNotHasKey('nin', $data);
                return Http::response([
                    'status' => 'success',
                    'data' => [
                        'tracking_id' => 'BTX947E60001020',
                        'firstname' => 'Fatima',
                        'lastname' => 'Aliyu',
                    ],
                ], 200);
            },
        ]);

        $customApi = CustomApi::create([
            'name' => 'DataVerify TID Test',
            'provider_identifier' => 'dataverify',
            'service_type' => 'nin_verification',
            'endpoint' => 'https://dataverify.org/developers/nin_api/',
            'api_key' => 'dv_test_key_123',
            'status' => true,
        ]);

        $client = new DataVerifyClient($customApi);
        $result = $client->verifyTracking('BTX947E60001020');

        $this->assertTrue($result['ok']);
        $this->assertSame('BTX947E60001020', $result['data']['tracking_id']);
        $this->assertSame('Fatima', $result['data']['firstname']);
    }

    public function test_robosttech_nin_verification_end_to_end(): void
    {
        $user = User::create([
            'fullname' => 'Robost User',
            'username' => 'u_robost_001',
            'email' => 'robost@example.com',
            'password' => Hash::make('Password@123'),
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();
        AccountBalance::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'user_balance' => 1000.00,
        ]);

        $robostApi = CustomApi::create([
            'name' => 'RobostTech API',
            'provider_identifier' => 'robosttech',
            'service_type' => 'nin_verification',
            'endpoint' => 'https://robosttech.com/api',
            'api_key' => 'robost_secret_token_123',
            'price' => 150.00,
            'status' => true,
            'priority' => 1,
        ]);

        CustomApiVerificationType::create([
            'custom_api_id' => $robostApi->id,
            'type_key' => 'nin',
            'label' => 'Search by NIN',
            'price' => 150.00,
            'status' => true,
            'sort_order' => 1,
            'meta' => ['path_suffix' => 'nin_verify'],
        ]);

        Http::fake([
            'https://robosttech.com/api/nin_verify' => function ($request) {
                $headers = $request->headers();
                $apiKey = $headers['api-key'][0] ?? null;
                $payload = $request->data();

                if ($apiKey !== 'robost_secret_token_123') {
                    return Http::response(['status' => false, 'message' => 'Unauthorized'], 401);
                }

                if (($payload['nin'] ?? $payload['number'] ?? null) !== '98765432101') {
                    return Http::response(['status' => false, 'message' => 'Invalid number'], 400);
                }

                return Http::response([
                    'status' => 'success',
                    'message' => 'Verification successful',
                    'data' => [
                        'nin' => '98765432101',
                        'firstname' => 'CHUKWUEMEKA',
                        'lastname' => 'OKONKWO',
                        'middlename' => 'DAVID',
                        'gender' => 'male',
                        'birthdate' => '1990-08-20',
                        'telephoneno' => '08012345678',
                        'photo' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY44YAAAAASUVORK5CYII=',
                    ],
                ], 200);
            },
        ]);

        $response = $this->actingAs($user)->postJson(route('services.nin.verify'), [
            'number' => '98765432101',
            'mode' => 'nin',
            'output_type' => 'premium_slip',
            'api_provider_id' => $robostApi->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Verification Successful',
        ]);

        $this->assertDatabaseHas('verification_results', [
            'user_id' => $user->id,
            'identifier' => '98765432101',
            'provider_name' => 'RobostTech API',
            'status' => 'success',
        ]);

        $this->assertSame(850.00, (float) $user->fresh()->wallet_balance);
    }

    public function test_robosttech_generates_in_house_premium_slip_and_bypasses_dataverify(): void
    {
        $user = User::create([
            'fullname' => 'Robost Slip User',
            'username' => 'u_robost_slip',
            'email' => 'robost-slip@example.com',
            'password' => Hash::make('Password@123'),
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        // ApiCenter configured with DataVerify key to prove it will NOT be called
        ApiCenter::create([
            'dataverify_api_key' => 'active_dataverify_key',
            'dataverify_endpoint_premium_slip' => 'https://dataverify.org/developers/nin_slips/nin_premium',
        ]);

        $result = VerificationResult::create([
            'user_id' => $user->id,
            'service_type' => 'nin_verification',
            'identifier' => '98765432101',
            'provider_name' => 'RobostTech API',
            'response_data' => [
                'nin' => '98765432101',
                'firstname' => 'CHUKWUEMEKA',
                'lastname' => 'OKONKWO',
                'middlename' => 'DAVID',
                'gender' => 'male',
                'birthdate' => '1990-08-20',
                'telephoneno' => '08012345678',
            ],
            'status' => 'success',
            'reference_id' => 'ROBOST-SLIP-001',
        ]);

        // If any request is sent to DataVerify, this will fail
        Http::fake([
            'https://dataverify.org/*' => function () {
                $this->fail('DataVerify should NOT have been invoked for Robosttech verification');
            },
        ]);

        $response = $this->actingAs($user)->get(route('services.nin.slip', [
            'id' => $result->id,
            'type' => 'premium_slip',
        ]));

        $response->assertStatus(200);
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('PREMIUM_SLIP_ROBOST-SLIP-001.pdf', (string) $response->headers->get('Content-Disposition'));

        // Binary begins with %PDF
        $this->assertStringStartsWith('%PDF', $response->getContent());

        // Verify cached source is in_house_premium_slip
        $freshResult = $result->fresh();
        $cachedSource = data_get($freshResult->response_data, '_cached_slips.premium_slip.source');
        $this->assertSame('in_house_premium_slip', $cachedSource);
    }

    public function test_robosttech_uses_in_house_premium_slip_even_when_standard_slip_requested(): void
    {
        $user = User::create([
            'fullname' => 'Robost Standard User',
            'username' => 'u_robost_std',
            'email' => 'robost-std@example.com',
            'password' => Hash::make('Password@123'),
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        ApiCenter::create([
            'dataverify_api_key' => 'active_dataverify_key',
        ]);

        $result = VerificationResult::create([
            'user_id' => $user->id,
            'service_type' => 'nin_verification',
            'identifier' => '98765432101',
            'provider_name' => 'Robosttech',
            'response_data' => [
                'nin' => '98765432101',
                'firstname' => 'CHUKWUEMEKA',
                'lastname' => 'OKONKWO',
            ],
            'status' => 'success',
            'reference_id' => 'ROBOST-SLIP-002',
        ]);

        Http::fake([
            'https://dataverify.org/*' => function () {
                $this->fail('DataVerify should NOT have been invoked');
            },
        ]);

        $response = $this->actingAs($user)->get(route('services.nin.slip', [
            'id' => $result->id,
            'type' => 'standard_slip',
        ]));

        $response->assertStatus(200);
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('PREMIUM_SLIP_ROBOST-SLIP-002.pdf', (string) $response->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_robosttech_slip_renders_in_house_premium_slip_view_for_html_and_print(): void
    {
        $user = User::create([
            'fullname' => 'Robost Print User',
            'username' => 'u_robost_print',
            'email' => 'robost-print@example.com',
            'password' => Hash::make('Password@123'),
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        $result = VerificationResult::create([
            'user_id' => $user->id,
            'service_type' => 'nin_verification',
            'identifier' => '98765432101',
            'provider_name' => 'Robosttech',
            'response_data' => [
                'nin' => '98765432101',
                'firstname' => 'CHUKWUEMEKA',
                'lastname' => 'OKONKWO',
            ],
            'status' => 'success',
            'reference_id' => 'ROBOST-SLIP-003',
        ]);

        $response = $this->actingAs($user)->get(route('services.nin.slip', [
            'id' => $result->id,
            'type' => 'standard_slip',
            'html' => '1',
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('pdf.nin_premium_slip');
        $response->assertSee('CHUKWUEMEKA');
        $response->assertSee('OKONKWO');
    }
}
