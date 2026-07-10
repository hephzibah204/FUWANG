<?php

namespace Tests\Feature;

use App\Models\AccountBalance;
use App\Models\ApiToken;
use App\Models\CustomApi;
use App\Models\DeveloperApiEndpoint;
use App\Models\DeveloperApiRequestLog;
use App\Models\SystemSetting;
use App\Models\Transaction;
use App\Models\User;
use App\Services\DeveloperApi\DeveloperApiCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DeveloperApiControlsTest extends TestCase
{
    use RefreshDatabase;

    private function makeApprovedUser(string $email = 'api@example.com'): array
    {
        $user = User::create([
            'fullname' => 'API User',
            'username' => 'api_' . substr(md5($email), 0, 8),
            'email' => $email,
            'password' => Hash::make('Password@123'),
            'api_access_status' => 'approved',
        ]);

        AccountBalance::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'user_balance' => 5000,
            'api_key' => 'user',
        ]);

        $plain = 'plain-' . substr(md5($email . microtime()), 0, 12);
        $token = ApiToken::create([
            'user_id' => $user->id,
            'name' => 'test',
            'token_hash' => hash('sha256', $plain),
            'last_four' => substr($plain, -4),
            'abilities' => ['*'],
            'rate_limit_per_minute' => 60,
        ]);

        return [$user, $token, $plain];
    }

    public function test_disabled_endpoint_returns_403_for_authenticated_api_user(): void
    {
        [$user, $token, $plain] = $this->makeApprovedUser('disabled@example.com');

        app(DeveloperApiCatalog::class)->ensureDefaults();
        DeveloperApiEndpoint::query()->where('slug', 'auth.me')->update(['is_enabled' => false]);

        $this->withHeader('Authorization', 'Bearer nx_' . $plain)
            ->getJson('/api/v1/me')
            ->assertStatus(403)
            ->assertJsonPath('error', 'endpoint_disabled');
    }

    public function test_enabled_endpoint_logs_usage_with_site_metadata(): void
    {
        [$user, $token, $plain] = $this->makeApprovedUser('usage@example.com');

        $this->withHeaders([
            'Authorization' => 'Bearer nx_' . $plain,
            'Origin' => 'https://client.example.com',
            'Referer' => 'https://client.example.com/docs',
        ])->getJson('/api/v1/me')
            ->assertOk();

        $this->assertDatabaseHas('developer_api_request_logs', [
            'api_token_id' => $token->id,
            'user_id' => $user->id,
            'endpoint_slug' => 'auth.me',
            'status_code' => 200,
            'origin_host' => 'client.example.com',
            'referer_host' => 'client.example.com',
        ]);
    }

    public function test_nin_api_uses_developer_specific_price_override(): void
    {
        [$user, $token, $plain] = $this->makeApprovedUser('pricing@example.com');

        SystemSetting::set('developer_api_nin_price', '321', 'developer_api');

        CustomApi::create([
            'name' => 'Generic NIN API',
            'service_type' => 'nin_verification',
            'endpoint' => 'https://example.com/nin',
            'headers' => [],
            'status' => true,
            'price' => 999,
        ]);

        Http::fake([
            'https://example.com/*' => Http::response([
                'status' => 'success',
                'data' => ['nin' => '12345678901', 'fullname' => 'Test Person'],
            ], 200),
        ]);

        $this->withHeader('Authorization', 'Bearer nx_' . $plain)
            ->postJson('/api/v1/verifications/nin', [
                'number' => '12345678901',
                'firstname' => 'John',
                'lastname' => 'Doe',
                'dob' => '1990-01-01',
                'mode' => 'nin',
            ])
            ->assertOk()
            ->assertJsonPath('status', true);

        $transaction = Transaction::query()
            ->where('user_email', $user->email)
            ->where('order_type', 'API: NIN Verification')
            ->latest()
            ->first();

        $this->assertNotNull($transaction);
        $this->assertSame(5000.0, (float) $transaction->balance_before);
        $this->assertSame(4679.0, (float) $transaction->balance_after);
    }
}

