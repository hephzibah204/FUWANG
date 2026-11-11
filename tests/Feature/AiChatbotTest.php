<?php

namespace Tests\Feature;

use App\Models\AccountBalance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiChatbotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure no external API key is active by default so fallback tests run instantly and deterministically
        config([
            'services.gemini.key' => null,
            'services.openai.key' => null,
        ]);
    }

    public function test_ai_chat_validates_message_presence(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->withoutMiddleware(ValidateCsrfToken::class)
            ->actingAs($user)
            ->postJson('/ai/chat', []);

        $response->assertStatus(422);
    }

    public function test_ai_chat_returns_personalized_balance_response(): void
    {
        $user = User::factory()->create([
            'fullname' => 'Emeka Okafor',
        ]);
        AccountBalance::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'user_balance' => 25400.50,
        ]);

        $response = $this
            ->withoutMiddleware(ValidateCsrfToken::class)
            ->actingAs($user)
            ->postJson('/ai/chat', [
                'message' => 'What is my current balance?'
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'status' => true,
        ]);
        $this->assertStringContainsString('25,400.50', $response->json('response'));
        $this->assertStringContainsString('/wallet/fund', $response->json('response'));
    }

    public function test_ai_chat_returns_nin_verification_guidance(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->withoutMiddleware(ValidateCsrfToken::class)
            ->actingAs($user)
            ->postJson('/ai/chat', [
                'message' => 'How can I do NIN verification?'
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'status' => true,
        ]);
        $this->assertStringContainsString('/services/identity/nin', $response->json('response'));
    }

    public function test_ai_chat_returns_airtime_and_data_guidance(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->withoutMiddleware(ValidateCsrfToken::class)
            ->actingAs($user)
            ->postJson('/ai/chat', [
                'message' => 'I want to buy MTN airtime and data'
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'status' => true,
        ]);
        $this->assertStringContainsString('/services/vtu/airtime', $response->json('response'));
        $this->assertStringContainsString('/services/vtu/data', $response->json('response'));
    }

    public function test_ai_chat_returns_virtual_card_guidance(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->withoutMiddleware(ValidateCsrfToken::class)
            ->actingAs($user)
            ->postJson('/ai/chat', [
                'message' => 'Do you have dollar virtual cards for Netflix?'
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'status' => true,
        ]);
        $this->assertStringContainsString('/services/virtual-card', $response->json('response'));
    }

    public function test_ai_chat_returns_enrollment_agent_guidance(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->withoutMiddleware(ValidateCsrfToken::class)
            ->actingAs($user)
            ->postJson('/ai/chat', [
                'message' => 'How can I become an enrollment agent?'
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'status' => true,
        ]);
        $this->assertStringContainsString('/agent/register', $response->json('response'));
    }

    public function test_ai_chat_handles_gemini_remote_api_success_when_reachable(): void
    {
        config(['services.gemini.key' => 'test-gemini-key']);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Hello from Gemini AI Assistant! We offer NIN and VTU services.']
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $user = User::factory()->create();

        $response = $this
            ->withoutMiddleware(ValidateCsrfToken::class)
            ->actingAs($user)
            ->postJson('/ai/chat', [
                'message' => 'Hello there!'
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'response' => 'Hello from Gemini AI Assistant! We offer NIN and VTU services.',
        ]);
    }

    public function test_ai_chat_handles_gemini_network_error_without_failing_user(): void
    {
        config(['services.gemini.key' => 'test-gemini-key']);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response('Server error', 500)
        ]);

        $user = User::factory()->create([
            'fullname' => 'Aisha Bello',
        ]);

        $response = $this
            ->withoutMiddleware(ValidateCsrfToken::class)
            ->actingAs($user)
            ->postJson('/ai/chat', [
                'message' => 'Hello, can you help me?'
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'status' => true,
        ]);
        // Must return friendly assistant fallback, NOT generic failure
        $this->assertStringContainsString('Aisha', $response->json('response'));
        $this->assertStringNotContainsString("I'm sorry, I encountered an error", $response->json('response'));
    }
}
