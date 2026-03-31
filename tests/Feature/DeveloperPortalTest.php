<?php

namespace Tests\Feature;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DeveloperPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_requires_login(): void
    {
        $this->get('/developer')->assertRedirect('/login');
    }

    public function test_user_can_view_portal_and_create_and_revoke_token(): void
    {
        $user = User::create([
            'fullname' => 'Dev User',
            'email' => 'dev@example.com',
            'password' => Hash::make('Password@123'),
        ]);

        $this->actingAs($user)
            ->get('/developer')
            ->assertOk();

        $res = $this->actingAs($user)
            ->postJson(route('developer.tokens.create'), [
                'name' => 'My App',
            ]);

        $res->assertOk()
            ->assertJson(['status' => true])
            ->assertJsonStructure(['token', 'token_id']);

        $this->assertDatabaseCount('api_tokens', 1);

        $tokenId = (int) $res->json('token_id');
        $token = ApiToken::findOrFail($tokenId);
        $this->assertNotNull($token->token_hash);
        $this->assertNull($token->revoked_at);

        $this->actingAs($user)
            ->postJson(route('developer.tokens.revoke', ['id' => $tokenId]))
            ->assertOk()
            ->assertJson(['status' => true]);

        $token->refresh();
        $this->assertNotNull($token->revoked_at);
    }

    public function test_openapi_download_requires_login(): void
    {
        $this->get(route('developer.openapi.v1'))->assertRedirect('/login');
    }
}

