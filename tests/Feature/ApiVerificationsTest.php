<?php

namespace Tests\Feature;

use App\Models\ApiToken;
use App\Models\CustomApi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApiVerificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_nin_verification_requires_auth(): void
    {
        $this->postJson('/api/v1/verifications/nin', [])->assertStatus(401);
    }

    public function test_nin_verification_success_stores_result(): void
    {
        $user = User::create([
            'fullname' => 'Api User',
            'email' => 'api@example.com',
            'password' => Hash::make('Password@123'),
        ]);

        $plain = 'plain-token-nin';
        ApiToken::create([
            'user_id' => $user->id,
            'name' => 'test',
            'token_hash' => hash('sha256', $plain),
            'last_four' => substr($plain, -4),
            'abilities' => ['*'],
            'rate_limit_per_minute' => 60,
        ]);

        CustomApi::create([
            'name' => 'VerifyMe',
            'service_type' => 'nin_verification',
            'endpoint' => 'https://example.com/nin',
            'headers' => [],
            'status' => true,
            'price' => 0,
        ]);

        Http::fake([
            'https://example.com/*' => Http::response([
                'status' => 'success',
                'data' => ['nin' => '123', 'fullname' => 'Test Person'],
            ], 200),
        ]);

        $res = $this->withHeader('Authorization', 'Bearer nx_' . $plain)
            ->postJson('/api/v1/verifications/nin', [
                'number' => '12345678901',
                'firstname' => 'A',
                'lastname' => 'B',
                'dob' => '1990-01-01',
                'mode' => 'nin',
            ]);

        $res->assertOk()
            ->assertJson(['status' => true])
            ->assertJsonStructure(['result_id', 'reference_id', 'data']);

        $this->assertDatabaseCount('verification_results', 1);
        $id = (int) $res->json('result_id');

        $this->withHeader('Authorization', 'Bearer nx_' . $plain)
            ->getJson('/api/v1/verifications/' . $id)
            ->assertOk()
            ->assertJson(['status' => true]);
    }
}

