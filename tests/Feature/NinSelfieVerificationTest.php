<?php

namespace Tests\Feature;

use App\Models\AccountBalance;
use App\Models\CustomApi;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Vuvaa\VuvaaCrypto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NinSelfieVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_run_in_person_selfie_verification_via_unified_nin_interface(): void
    {
        Storage::fake('local');
        Cache::flush();

        $user = User::factory()->create();
        AccountBalance::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'user_balance' => 1000,
            'api_key' => 'user',
        ]);

        SystemSetting::set('nin_face_verification_price', 50);

        CustomApi::create([
            'name' => 'VUVAA (In-Person)',
            'provider_identifier' => 'vuvaa',
            'service_type' => 'nin_face_verification',
            'endpoint' => 'https://premiere.vuvaa.com/demo/NIN_Validation_LIVE',
            'headers' => [],
            'config' => [
                'username' => 'demoUser',
                'password' => 'demoPass',
                'encryption_key' => 'FD!-F=15B46BAD21',
                'encryption_iv' => '0123456789012345',
                'in_person_path' => 'in_person_verification',
                'token_ttl_seconds' => 300,
                'token_ttl_buffer_seconds' => 0,
            ],
            'status' => true,
            'priority' => 1,
            'price' => 50,
            'timeout_seconds' => 10,
            'retry_count' => 0,
            'retry_delay_ms' => 0,
        ]);

        $crypto = new VuvaaCrypto('FD!-F=15B46BAD21', '0123456789012345');
        $loginB64 = $crypto->encryptToBase64(['code' => '00', 'accessToken' => 'abc123']);
        $verifyB64 = $crypto->encryptToBase64(['code' => '00', 'reference_id' => 'REF2', 'fname' => 'Ada', 'lname' => 'Lovelace', 'nin' => '74756011111']);

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

        $res = $this->actingAs($user)->post(route('services.nin.verify'), [
            'mode' => 'selfie',
            'number' => '74756011111',
            'selfie' => UploadedFile::fake()->image('selfie.jpg', 300, 300),
        ], ['Accept' => 'application/json']);

        $res->assertOk()->assertJson([
            'status' => true,
        ])->assertJsonStructure(['result_id', 'data']);

        $this->assertDatabaseHas('verification_results', [
            'user_id' => $user->id,
            'service_type' => 'nin_face_verification',
            'identifier' => '74756011111',
            'status' => 'success',
        ]);

        $files = Storage::disk('local')->allFiles('private/selfies/' . $user->id);
        $this->assertCount(1, $files);
    }
}
