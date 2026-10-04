<?php

namespace Tests\Feature;

use App\Models\AccountBalance;
use App\Models\ApiCenter;
use App\Models\CustomApi;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Vuvaa\VuvaaCrypto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NINProviderE2ETest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private VuvaaCrypto $crypto;
    private string $walletB64;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        $this->user = User::factory()->create();
        AccountBalance::create([
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'user_balance' => 5000,
            'api_key' => 'user-key',
        ]);

        SystemSetting::set('nin_price', 100);

        $this->crypto = new VuvaaCrypto('FD!-F=15B46BAD21', '0123456789012345');
        $this->walletB64 = $this->crypto->encryptToBase64(['code' => '00', 'wallet_units' => 100]);
    }

    private function setupVuvaaProvider(): CustomApi
    {
        return CustomApi::create([
            'name' => 'VUVAA (NIN)',
            'provider_identifier' => 'vuvaa',
            'service_type' => 'nin_verification',
            'endpoint' => 'https://premiere.vuvaa.com/demo/NIN_Validation_LIVE',
            'headers' => [],
            'config' => [
                'username' => 'demoUser',
                'password' => 'demoPass',
                'encryption_key' => 'FD!-F=15B46BAD21',
                'encryption_iv' => '0123456789012345',
                'verify_nin_path' => 'verify_nin',
                'token_ttl_seconds' => 300,
                'token_ttl_buffer_seconds' => 0,
            ],
            'status' => true,
            'priority' => 1,
            'price' => 100,
            'timeout_seconds' => 10,
        ]);
    }

    public function test_vuvaa_invalid_nin_fails_over_to_backup_provider_and_verifies_valid_nin(): void
    {
        $this->setupVuvaaProvider();

        // Configure legacy API as backup
        ApiCenter::create([
            'dataverify_api_key' => 'legacy-key',
            'dataverify_endpoint_nin' => 'https://dataverify.com.ng/developers/nin_api/',
        ]);

        $loginB64 = $this->crypto->encryptToBase64(['code' => '00', 'accessToken' => 'token123']);
        // VUVAA in demo mode returns "Invalid NIN" for live citizen NIN
        $invalidNinB64 = $this->crypto->encryptToBase64([
            'code' => '99',
            'statusCode' => '99',
            'message' => 'Verification failed: Invalid NIN',
        ]);

        $backupCalled = false;
        $receivedNin = null;

        Http::fake([
            'https://premiere.vuvaa.com/*/login' => Http::response(['payload' => $loginB64], 200),
            'https://premiere.vuvaa.com/*/get_wallet_details' => Http::response(['payload' => $this->walletB64], 200),
            'https://premiere.vuvaa.com/*/verify_nin' => Http::response(['payload' => $invalidNinB64], 200),
            'https://dataverify.org/*' => function ($request) use (&$backupCalled, &$receivedNin) {
                $backupCalled = true;
                $receivedNin = $request['nin'] ?? null;
                return Http::response([
                    'status' => 'success',
                    'response_code' => '00',
                    'data' => [
                        'nin' => '12345678901',
                        'firstname' => 'Abiodun',
                        'lastname' => 'Gbadamosi',
                    ],
                ], 200);
            },
        ]);

        // Test with spaces in the NIN to verify input sanitization as well
        $res = $this->actingAs($this->user)->post(route('services.nin.verify'), [
            'mode' => 'nin',
            'number' => '1234 5678 901',
        ], ['Accept' => 'application/json']);

        $res->assertOk();
        $res->assertJson([
            'status' => true,
        ]);

        $this->assertTrue($backupCalled, 'Backup provider must be called when primary reports Invalid NIN on a valid citizen NIN');
        $this->assertEquals('12345678901', $receivedNin, 'NIN with spaces must be sanitized to pure digits');
    }

    public function test_all_providers_failing_with_invalid_nin_returns_clean_error(): void
    {
        $this->setupVuvaaProvider();

        ApiCenter::create([
            'dataverify_api_key' => 'legacy-key',
            'dataverify_endpoint_nin' => 'https://dataverify.org/developers/nin_api/',
        ]);

        $loginB64 = $this->crypto->encryptToBase64(['code' => '00', 'accessToken' => 'token123']);
        $invalidNinB64 = $this->crypto->encryptToBase64([
            'code' => '99',
            'statusCode' => '99',
            'message' => 'Verification failed: Invalid NIN',
        ]);

        Http::fake([
            'https://premiere.vuvaa.com/*/login' => Http::response(['payload' => $loginB64], 200),
            'https://premiere.vuvaa.com/*/get_wallet_details' => Http::response(['payload' => $this->walletB64], 200),
            'https://premiere.vuvaa.com/*/verify_nin' => Http::response(['payload' => $invalidNinB64], 200),
            'https://dataverify.org/*' => Http::response([
                'status' => 'error',
                'message' => 'Verification failed: Invalid NIN',
            ], 400),
        ]);

        $res = $this->actingAs($this->user)->post(route('services.nin.verify'), [
            'mode' => 'nin',
            'number' => '12345678901',
        ], ['Accept' => 'application/json']);

        $res->assertOk();
        $res->assertJson([
            'status' => false,
            'message' => 'Verification failed: Invalid NIN',
        ]);
    }

    public function test_legacy_api_domain_is_normalized_to_org_when_failover_occurs(): void
    {
        $this->setupVuvaaProvider();

        // Legacy API configured with .com.ng in database
        ApiCenter::create([
            'dataverify_api_key' => 'legacy-key',
            'dataverify_endpoint_nin' => 'https://dataverify.com.ng/developers/nin_api/',
        ]);

        $loginB64 = $this->crypto->encryptToBase64(['code' => '00', 'accessToken' => 'token123']);
        // Non-terminal provider failure (e.g. gateway 502 / timeout)
        $serverErrorB64 = $this->crypto->encryptToBase64([
            'code' => '500',
            'statusCode' => '500',
            'message' => 'Gateway timeout connecting to NIMC',
        ]);

        $calledOrgDomain = false;
        $calledComNgDomain = false;

        Http::fake([
            'https://premiere.vuvaa.com/*/login' => Http::response(['payload' => $loginB64], 200),
            'https://premiere.vuvaa.com/*/get_wallet_details' => Http::response(['payload' => $this->walletB64], 200),
            'https://premiere.vuvaa.com/*/verify_nin' => Http::response(['payload' => $serverErrorB64], 200),
            'https://dataverify.org/*' => function ($request) use (&$calledOrgDomain) {
                $calledOrgDomain = true;
                return Http::response([
                    'status' => 'success',
                    'response_code' => '00',
                    'data' => [
                        'nin' => '12345678901',
                        'firstname' => 'Test',
                        'lastname' => 'User',
                    ],
                ], 200);
            },
            '*dataverify.com.ng*' => function () use (&$calledComNgDomain) {
                $calledComNgDomain = true;
                return Http::response([], 500);
            },
        ]);

        $res = $this->actingAs($this->user)->post(route('services.nin.verify'), [
            'mode' => 'nin',
            'number' => '12345678901',
        ], ['Accept' => 'application/json']);

        $res->assertOk();
        $res->assertJson([
            'status' => true,
        ]);

        $this->assertTrue($calledOrgDomain, 'Request should have been routed to dataverify.org');
        $this->assertFalse($calledComNgDomain, 'Request must never hit dead dataverify.com.ng host');
    }

    public function test_legacy_api_connection_exception_is_caught_gracefully_without_exposing_curl_errors(): void
    {
        $this->setupVuvaaProvider();

        ApiCenter::create([
            'dataverify_api_key' => 'legacy-key',
            'dataverify_endpoint_nin' => 'https://dataverify.com.ng/developers/nin_api/',
        ]);

        $loginB64 = $this->crypto->encryptToBase64(['code' => '00', 'accessToken' => 'token123']);
        $serverErrorB64 = $this->crypto->encryptToBase64([
            'code' => '503',
            'statusCode' => '503',
            'message' => 'Service temporarily unavailable',
        ]);

        Http::fake([
            'https://premiere.vuvaa.com/*/login' => Http::response(['payload' => $loginB64], 200),
            'https://premiere.vuvaa.com/*/get_wallet_details' => Http::response(['payload' => $this->walletB64], 200),
            'https://premiere.vuvaa.com/*/verify_nin' => Http::response(['payload' => $serverErrorB64], 200),
            'https://dataverify.org/*' => function () {
                throw new ConnectionException('cURL error 6: Could not resolve host: dataverify.org');
            },
        ]);

        $res = $this->actingAs($this->user)->post(route('services.nin.verify'), [
            'mode' => 'nin',
            'number' => '12345678901',
        ], ['Accept' => 'application/json']);

        $res->assertOk();
        $res->assertJson(['status' => false]);

        $message = $res->json('message');
        $this->assertStringNotContainsString('cURL error', $message);
        $this->assertStringNotContainsString('Could not resolve host', $message);
        $this->assertStringNotContainsString('Legacy API:', $message);
    }
}
