<?php

namespace Tests\Unit;

use App\Models\CustomApi;
use App\Services\Vuvaa\VuvaaClient;
use App\Services\Vuvaa\VuvaaCrypto;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VuvaaClientTest extends TestCase
{
    public function test_crypto_roundtrip(): void
    {
        $crypto = new VuvaaCrypto('FD!-F=15B46BAD21', '0123456789012345');
        $data = ['username' => 'dorisVal', 'nin' => '74756011111'];

        $b64 = $crypto->encryptToBase64($data);
        $out = $crypto->decryptBase64ToArray($b64);

        $this->assertSame($data, $out);
    }

    public function test_verify_nin_logs_in_and_sends_bearer_token(): void
    {
        Cache::flush();

        $provider = new CustomApi([
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
            'timeout_seconds' => 10,
        ]);

        $crypto = new VuvaaCrypto('FD!-F=15B46BAD21', '0123456789012345');
        $loginResponse = ['code' => '00', 'accessToken' => 'abc123'];
        $verifyResponse = ['code' => '00', 'transaction_id' => 'T1', 'reference_id' => 'REF1', 'fname' => 'Ada'];

        $loginB64 = $crypto->encryptToBase64($loginResponse);
        $verifyB64 = $crypto->encryptToBase64($verifyResponse);

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

        $client = new VuvaaClient($provider);
        $result = $client->verifyNin('74756011111', 'REF1');

        $this->assertTrue($result['ok']);
        $this->assertSame('00', $result['data']['code']);

        Http::assertSent(function ($request) {
            if (!str_ends_with((string) $request->url(), '/verify_nin')) {
                return false;
            }
            $auth = $request->header('Authorization');
            return is_array($auth) && ($auth[0] ?? '') === 'Bearer abc123';
        });
    }

    public function test_in_person_verification_sends_bearer_token_and_hits_configured_path(): void
    {
        Cache::flush();

        $provider = new CustomApi([
            'provider_identifier' => 'vuvaa',
            'service_type' => 'nin_face_verification',
            'endpoint' => 'https://premiere.vuvaa.com/demo/NIN_Validation_LIVE',
            'headers' => [],
            'config' => [
                'username' => 'demoUser',
                'password' => 'demoPass',
                'encryption_key' => 'FD!-F=15B46BAD21',
                'encryption_iv' => '0123456789012345',
                'token_ttl_seconds' => 300,
                'token_ttl_buffer_seconds' => 0,
                'in_person_path' => 'in_person_verification',
                'selfie_field' => 'image',
            ],
            'timeout_seconds' => 10,
        ]);

        $crypto = new VuvaaCrypto('FD!-F=15B46BAD21', '0123456789012345');
        $loginResponse = ['code' => '00', 'accessToken' => 'abc123'];
        $verifyResponse = ['code' => '00', 'transaction_id' => 'T2', 'reference_id' => 'REF2', 'fname' => 'Ada'];

        $loginB64 = $crypto->encryptToBase64($loginResponse);
        $verifyB64 = $crypto->encryptToBase64($verifyResponse);

        Http::fake(function ($request) use ($loginB64, $verifyB64) {
            $url = (string) $request->url();
            if (str_ends_with($url, '/login')) {
                return Http::response(['payload' => $loginB64], 200);
            }
            if (str_ends_with($url, '/in_person_verification')) {
                return Http::response(['payload' => $verifyB64], 200);
            }
            return Http::response(['message' => 'not found'], 404);
        });

        $client = new VuvaaClient($provider);
        $result = $client->verifyInPerson('74756011111', base64_encode('x'), 'REF2');

        $this->assertTrue($result['ok']);
        $this->assertSame('00', $result['data']['code']);

        Http::assertSent(function ($request) {
            if (!str_ends_with((string) $request->url(), '/in_person_verification')) {
                return false;
            }
            $auth = $request->header('Authorization');
            return is_array($auth) && ($auth[0] ?? '') === 'Bearer abc123';
        });
    }

    public function test_share_code_verification_hits_share_code_endpoint(): void
    {
        Cache::flush();

        $provider = new CustomApi([
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
                'share_code_path' => 'share_code',
                'share_code_field' => 'share_code',
            ],
            'timeout_seconds' => 10,
        ]);

        $crypto = new VuvaaCrypto('FD!-F=15B46BAD21', '0123456789012345');
        $loginB64 = $crypto->encryptToBase64(['code' => '00', 'accessToken' => 'abc123']);
        $shareB64 = $crypto->encryptToBase64(['code' => '00', 'reference_id' => 'REFS1', 'fname' => 'Ada']);

        Http::fake(function ($request) use ($loginB64, $shareB64) {
            $url = (string) $request->url();
            if (str_ends_with($url, '/login')) {
                return Http::response(['payload' => $loginB64], 200);
            }
            if (str_ends_with($url, '/share_code')) {
                return Http::response(['payload' => $shareB64], 200);
            }
            return Http::response(['message' => 'not found'], 404);
        });

        $client = new VuvaaClient($provider);
        $result = $client->verifyShareCode('ABC123', 'REFS1');

        $this->assertTrue($result['ok']);
        $this->assertSame('00', $result['data']['code']);
    }

    public function test_requery_hits_requery_endpoint(): void
    {
        Cache::flush();

        $provider = new CustomApi([
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
                'requery_path' => 'requery',
            ],
            'timeout_seconds' => 10,
        ]);

        $crypto = new VuvaaCrypto('FD!-F=15B46BAD21', '0123456789012345');
        $loginB64 = $crypto->encryptToBase64(['code' => '00', 'accessToken' => 'abc123']);
        $requeryB64 = $crypto->encryptToBase64(['code' => '00', 'reference_id' => 'REFX', 'session_complete' => '1']);

        Http::fake(function ($request) use ($loginB64, $requeryB64) {
            $url = (string) $request->url();
            if (str_ends_with($url, '/login')) {
                return Http::response(['payload' => $loginB64], 200);
            }
            if (str_ends_with($url, '/requery')) {
                return Http::response(['payload' => $requeryB64], 200);
            }
            return Http::response(['message' => 'not found'], 404);
        });

        $client = new VuvaaClient($provider);
        $result = $client->requery('REFX');

        $this->assertTrue($result['ok']);
        $this->assertSame('00', $result['data']['code']);
    }

    public function test_create_user_does_not_send_bearer_token(): void
    {
        Cache::flush();

        $provider = new CustomApi([
            'provider_identifier' => 'vuvaa',
            'service_type' => 'nin_verification',
            'endpoint' => 'https://premiere.vuvaa.com/demo/NIN_Validation_LIVE',
            'headers' => [],
            'config' => [
                'encryption_key' => 'FD!-F=15B46BAD21',
                'encryption_iv' => '0123456789012345',
                'create_user_path' => 'create_user',
            ],
            'timeout_seconds' => 10,
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

        $client = new VuvaaClient($provider);
        $res = $client->createUser([
            'email' => 'a@b.com',
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

        $this->assertTrue($res['ok']);

        Http::assertSent(function ($request) {
            if (!str_ends_with((string) $request->url(), '/create_user')) {
                return false;
            }
            $auth = $request->header('Authorization');
            return empty($auth);
        });
    }
}
