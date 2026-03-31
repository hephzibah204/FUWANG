<?php

namespace Tests\Feature;

use App\Models\AccountBalance;
use App\Models\CustomApi;
use App\Models\User;
use App\Services\Vuvaa\VuvaaCrypto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NinShareCodeAndRequeryTest extends TestCase
{
    use RefreshDatabase;

    public function test_share_code_verification_stores_result(): void
    {
        Cache::flush();

        $user = User::factory()->create();
        AccountBalance::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'user_balance' => 1000,
            'api_key' => 'user',
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
                'share_code_path' => 'share_code',
                'share_code_field' => 'share_code',
                'token_ttl_seconds' => 300,
                'token_ttl_buffer_seconds' => 0,
            ],
            'status' => true,
            'priority' => 1,
            'price' => 0,
        ]);

        $crypto = new VuvaaCrypto('FD!-F=15B46BAD21', '0123456789012345');
        $loginB64 = $crypto->encryptToBase64(['code' => '00', 'accessToken' => 'abc123']);
        $shareB64 = $crypto->encryptToBase64(['code' => '00', 'reference_id' => 'REFS1', 'fname' => 'Ada', 'nin' => '74756011111']);

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

        $res = $this->actingAs($user)->post(route('services.nin.verify'), [
            'mode' => 'share_code',
            'share_code' => 'ABC123',
        ], ['Accept' => 'application/json']);

        $res->assertOk()->assertJson(['status' => true])->assertJsonStructure(['result_id', 'data']);

        $this->assertDatabaseHas('verification_results', [
            'user_id' => $user->id,
            'service_type' => 'nin_verification',
            'identifier' => 'ABC123',
            'status' => 'success',
        ]);
    }

    public function test_requery_stores_result(): void
    {
        Cache::flush();

        $user = User::factory()->create();
        AccountBalance::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'user_balance' => 1000,
            'api_key' => 'user',
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
                'requery_path' => 'requery',
                'token_ttl_seconds' => 300,
                'token_ttl_buffer_seconds' => 0,
            ],
            'status' => true,
            'priority' => 1,
            'price' => 0,
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

        $res = $this->actingAs($user)->post(route('services.nin.verify'), [
            'mode' => 'requery',
            'reference_id' => 'REFX',
        ], ['Accept' => 'application/json']);

        $res->assertOk()->assertJson(['status' => true])->assertJsonStructure(['result_id', 'data']);

        $this->assertDatabaseHas('verification_results', [
            'user_id' => $user->id,
            'service_type' => 'nin_verification',
            'identifier' => 'REFX',
            'status' => 'success',
        ]);
    }
}

