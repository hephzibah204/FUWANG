<?php

namespace Tests\Feature;

use App\Models\ApiToken;
use App\Models\CustomApi;
use App\Models\User;
use App\Services\Vuvaa\VuvaaCrypto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApiVuvaaEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_vuvaa_verify_nin_proxy_stores_result(): void
    {
        $user = User::create([
            'fullname' => 'Api User',
            'email' => 'api@example.com',
            'password' => Hash::make('Password@123'),
        ]);

        $plain = 'plain-token-vuvaa';
        ApiToken::create([
            'user_id' => $user->id,
            'name' => 'test',
            'token_hash' => hash('sha256', $plain),
            'last_four' => substr($plain, -4),
            'abilities' => ['*'],
            'rate_limit_per_minute' => 60,
        ]);

        CustomApi::create([
            'name' => 'VUVAA',
            'provider_identifier' => 'vuvaa',
            'service_type' => 'nin_verification',
            'endpoint' => 'https://premiere.vuvaa.com/demo/NIN_Validation_LIVE',
            'headers' => [],
            'config' => [
                'username' => 'demoUser',
                'password' => 'demoPass',
                'encryption_key' => 'FD!-F=15B46BAD21',
                'encryption_iv' => '0123456789012345',
                'token_ttl_seconds' => 300,
                'token_ttl_buffer_seconds' => 0,
            ],
            'status' => true,
            'priority' => 1,
        ]);

        $crypto = new VuvaaCrypto('FD!-F=15B46BAD21', '0123456789012345');
        $loginB64 = $crypto->encryptToBase64(['code' => '00', 'accessToken' => 'abc123']);
        $verifyB64 = $crypto->encryptToBase64(['code' => '00', 'reference_id' => 'R1', 'fname' => 'Ada', 'nin' => '74756011111']);

        Http::fake(function ($request) use ($loginB64, $verifyB64) {
            $url = (string) $request->url();
            if (str_ends_with($url, '/login')) {
                return Http::response(['payload' => $loginB64], 200);
            }
            if (str_ends_with($url, '/verify_nin')) {
                return Http::response(['payload' => $verifyB64], 200);
            }
            return Http::response(['message' => 'not found'], 404);
        });

        $res = $this->withHeader('Authorization', 'Bearer nx_' . $plain)
            ->postJson('/api/v1/vuvaa/verify_nin', [
                'nin' => '74756011111',
                'reference_id' => 'R1',
            ]);

        $res->assertOk()->assertJson(['status' => true])->assertJsonStructure(['data']);

        $this->assertDatabaseHas('verification_results', [
            'user_id' => $user->id,
            'service_type' => 'vuvaa_verify_nin',
            'identifier' => '74756011111',
        ]);
    }

    public function test_vuvaa_create_user_proxy_stores_result(): void
    {
        $user = User::create([
            'fullname' => 'Api User',
            'email' => 'api2@example.com',
            'password' => Hash::make('Password@123'),
        ]);

        $plain = 'plain-token-vuvaa2';
        ApiToken::create([
            'user_id' => $user->id,
            'name' => 'test',
            'token_hash' => hash('sha256', $plain),
            'last_four' => substr($plain, -4),
            'abilities' => ['*'],
            'rate_limit_per_minute' => 60,
        ]);

        CustomApi::create([
            'name' => 'VUVAA',
            'provider_identifier' => 'vuvaa',
            'service_type' => 'nin_verification',
            'endpoint' => 'https://premiere.vuvaa.com/demo/NIN_Validation_LIVE',
            'headers' => [],
            'config' => [
                'encryption_key' => 'FD!-F=15B46BAD21',
                'encryption_iv' => '0123456789012345',
            ],
            'status' => true,
            'priority' => 1,
        ]);

        $crypto = new VuvaaCrypto('FD!-F=15B46BAD21', '0123456789012345');
        $createB64 = $crypto->encryptToBase64(['code' => '00', 'message' => 'Created']);

        Http::fake(function ($request) use ($createB64) {
            $url = (string) $request->url();
            if (str_ends_with($url, '/create_user')) {
                return Http::response(['payload' => $createB64], 200);
            }
            return Http::response(['message' => 'not found'], 404);
        });

        $res = $this->withHeader('Authorization', 'Bearer nx_' . $plain)->postJson('/api/v1/vuvaa/create_user', [
            'email' => 'x@y.com',
            'password' => 'secret',
            'firstname' => 'A',
            'lastname' => 'B',
            'username' => 'u1',
            'dob' => '01-01-1990',
            'gender' => 'Male',
            'address' => 'addr',
            'state' => 'state',
            'phone' => '08000000000',
            'account_level' => '1',
            'enterprise_id' => 'ENT',
            'ip_addresses' => ['127.0.0.1'],
            'ip_val_flag' => 0,
        ]);

        $res->assertOk()->assertJson(['status' => true])->assertJsonStructure(['data']);

        $this->assertDatabaseHas('verification_results', [
            'user_id' => $user->id,
            'service_type' => 'vuvaa_create_user',
            'identifier' => 'u1',
        ]);
    }
}

