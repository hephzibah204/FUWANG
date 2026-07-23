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
    private CustomApi $provider;
    private VuvaaCrypto $crypto;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        $this->provider = new CustomApi([
            'provider_identifier' => 'vuvaa',
            'service_type' => 'nin_verification',
            'endpoint' => 'https://verificationservice.vuvaa.com/NIN_Validation',
            'headers' => [],
            'config' => [
                'username' => 'demoUser',
                'password' => 'demoPass',
                'encryption_key' => 'some-32-byte-secrect-key-for-vuvaa',
                'encryption_iv' => 'a-16-byte-iv-key',
            ],
            'timeout_seconds' => 10,
        ]);

        $this->crypto = new VuvaaCrypto(
            $this->provider->config['encryption_key'],
            $this->provider->config['encryption_iv']
        );
    }

    private function getLoginFake(): array
    {
        $loginResponse = [
            'statusCode' => '00',
            'message' => 'Login successful',
            'data' => ['access_token' => 'abc123xyz'],
        ];

        return [
            '*.vuvaa.com/NIN_Validation/login' => Http::response(['payload' => $this->crypto->encryptToBase64($loginResponse)]),
        ];
    }

    public function test_verify_nin_sends_correct_payload_and_auth(): void
    {
        $verifyResponse = ['statusCode' => '00', 'message' => 'Success', 'data' => ['fname' => 'Ada']];

        Http::fake(array_merge($this->getLoginFake(), [
            '*.vuvaa.com/NIN_Validation/verify_nin' => Http::response(['payload' => $this->crypto->encryptToBase64($verifyResponse)]),
            '*' => Http::response(['message' => 'Fallback: Not Found'], 404),
        ]));

        $client = new VuvaaClient($this->provider);
        $result = $client->verifyNin('12345678901', 'test-ref');

        $this->assertTrue($result['ok']);
        $this->assertEquals('Ada', $result['data']['data']['fname']);

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Bearer abc123xyz') &&
                   str_ends_with($request->url(), '/verify_nin');
        });
    }

    public function test_in_person_verification_sends_correct_payload(): void
    {
        $verifyResponse = ['statusCode' => '00', 'message' => 'Success', 'data' => ['match' => true]];

        Http::fake(array_merge($this->getLoginFake(), [
            '*.vuvaa.com/NIN_Validation/in_person_verification' => Http::response(['payload' => $this->crypto->encryptToBase64($verifyResponse)]),
            '*' => Http::response(['message' => 'Fallback: Not Found'], 404),
        ]));

        $client = new VuvaaClient($this->provider);
        $result = $client->verifyInPerson('12345678901', base64_encode('test-image'));

        $this->assertTrue($result['ok']);
        $this->assertTrue($result['data']['data']['match']);

        Http::assertSent(function ($request) {
            $payload = json_decode($request->body(), true)['payload'];
            $decrypted = $this->crypto->decryptBase64ToArray($payload);
            return isset($decrypted['selfieImage']) && str_ends_with($request->url(), '/in_person_verification');
        });
    }

    public function test_verify_share_code_sends_correct_payload(): void
    {
        $response = ['statusCode' => '00', 'message' => 'Success'];
        Http::fake(array_merge($this->getLoginFake(), [
            '*.vuvaa.com/NIN_Validation/share_code' => Http::response(['payload' => $this->crypto->encryptToBase64($response)]),
        ]));

        $client = new VuvaaClient($this->provider);
        $result = $client->verifyShareCode('ABCDEF');

        $this->assertTrue($result['ok']);
        Http::assertSent(function ($request) {
            $payload = json_decode($request->body(), true)['payload'];
            $decrypted = $this->crypto->decryptBase64ToArray($payload);
            return isset($decrypted['shareCode']) && $decrypted['shareCode'] === 'ABCDEF' && str_ends_with($request->url(), '/share_code');
        });
    }

    public function test_requery_sends_correct_payload(): void
    {
        $response = ['statusCode' => '00', 'message' => 'Found'];
        Http::fake(array_merge($this->getLoginFake(), [
            '*.vuvaa.com/NIN_Validation/requery' => Http::response(['payload' => $this->crypto->encryptToBase64($response)]),
        ]));

        $client = new VuvaaClient($this->provider);
        $result = $client->requery('test-ref-123');

        $this->assertTrue($result['ok']);
        Http::assertSent(function ($request) {
            $payload = json_decode($request->body(), true)['payload'];
            $decrypted = $this->crypto->decryptBase64ToArray($payload);
            return isset($decrypted['reference_id']) && $decrypted['reference_id'] === 'test-ref-123' && str_ends_with($request->url(), '/requery');
        });
    }

    public function test_get_wallet_details_hits_correct_endpoint(): void
    {
        $response = ['statusCode' => '00', 'data' => ['balance' => 1000]];
        Http::fake(array_merge($this->getLoginFake(), [
            '*.vuvaa.com/NIN_Validation/get_wallet_details' => Http::response(['payload' => $this->crypto->encryptToBase64($response)]),
        ]));

        $client = new VuvaaClient($this->provider);
        $result = $client->getWalletDetails();

        $this->assertTrue($result['ok']);
        $this->assertEquals(1000, $result['data']['data']['balance']);
    }

    public function test_transaction_history_hits_correct_endpoint(): void
    {
        $response = ['statusCode' => '00', 'data' => [['id' => 1]]];
        Http::fake(array_merge($this->getLoginFake(), [
            '*.vuvaa.com/NIN_Validation/transaction_history' => Http::response(['payload' => $this->crypto->encryptToBase64($response)]),
        ]));

        $client = new VuvaaClient($this->provider);
        $result = $client->transactionHistory(['from' => '2023-01-01']);

        $this->assertTrue($result['ok']);
        Http::assertSent(function ($request) {
            $payload = json_decode($request->body(), true)['payload'];
            $decrypted = $this->crypto->decryptBase64ToArray($payload);
            return isset($decrypted['from']) && $decrypted['from'] === '2023-01-01';
        });
    }

    public function test_get_reasons_hits_correct_endpoint(): void
    {
        $response = ['statusCode' => '00', 'data' => ['Reason 1']];
        Http::fake(array_merge($this->getLoginFake(), [
            '*.vuvaa.com/NIN_Validation/getReasons' => Http::response(['payload' => $this->crypto->encryptToBase64($response)]),
        ]));

        $client = new VuvaaClient($this->provider);
        $result = $client->getReasons();

        $this->assertTrue($result['ok']);
    }

    public function test_create_user_does_not_send_auth_token(): void
    {
        $response = ['statusCode' => '00', 'message' => 'User created'];
        Http::fake(['*.vuvaa.com/NIN_Validation/create_user' => Http::response(['payload' => $this->crypto->encryptToBase64($response)])]);

        $client = new VuvaaClient($this->provider);
        $result = $client->createUser(['name' => 'Test']);

        $this->assertTrue($result['ok']);
        Http::assertSent(function ($request) {
            return !$request->hasHeader('Authorization');
        });
    }

    public function test_handles_insufficient_funds_error(): void
    {
        $response = ['statusCode' => '51', 'message' => 'Insufficient Funds'];
        Http::fake(array_merge($this->getLoginFake(), [
            '*' => Http::response(['payload' => $this->crypto->encryptToBase64($response)]),
        ]));

        $client = new VuvaaClient($this->provider);
        $result = $client->verifyNin('12345678901');

        $this->assertFalse($result['ok']);
        $this->assertEquals('Insufficient Funds', $result['message']);
    }

    public function test_handles_login_failure(): void
    {
        Http::fake(['*.vuvaa.com/NIN_Validation/login' => Http::response(['message' => 'Invalid credentials'], 401)]);

        $client = new VuvaaClient($this->provider);
        $token = $client->getAccessToken();

        $this->assertNull($token);
    }

    public function test_token_is_reused_from_cache(): void
    {
        Cache::put('vuvaa.token.' . md5('demoUser'), 'cached-token', 3600);

        Http::fake([
            '*.vuvaa.com/NIN_Validation/verify_nin' => Http::response(['payload' => $this->crypto->encryptToBase64(['statusCode' => '00'])]),
            '*' => Http::response(['message' => 'Fallback: Not Found'], 404),
        ]);

        $client = new VuvaaClient($this->provider);
        $client->verifyNin('12345678901');

        Http::assertNotSent(function ($request) {
            return str_ends_with($request->url(), '/login');
        });

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Bearer cached-token');
        });
    }
}