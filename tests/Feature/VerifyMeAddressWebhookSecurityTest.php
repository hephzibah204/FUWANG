<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifyMeAddressWebhookSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_verifyme_webhook_rejects_when_secret_not_configured(): void
    {
        SystemSetting::put('verifyme_webhook_secret', '', 'security');

        $payload = [
            'data' => [
                'reference' => 'VMN_TEST_REF',
                'status' => ['status' => 'Completed'],
            ],
        ];

        $this->postJson(route('webhooks.verifyme.address'), $payload)
            ->assertStatus(503);
    }

    public function test_verifyme_webhook_rejects_when_signature_missing(): void
    {
        SystemSetting::put('verifyme_webhook_secret', 'test-secret', 'security');

        $payload = [
            'data' => [
                'reference' => 'VMN_TEST_REF',
                'status' => ['status' => 'Completed'],
            ],
        ];

        $this->postJson(route('webhooks.verifyme.address'), $payload)
            ->assertStatus(403);
    }

    public function test_verifyme_webhook_accepts_with_valid_signature(): void
    {
        SystemSetting::put('verifyme_webhook_secret', 'test-secret', 'security');

        $payload = [
            'data' => [
                'reference' => 'VMN_TEST_REF',
                'status' => ['status' => 'Completed'],
            ],
        ];

        $raw = json_encode($payload);
        $sig = hash_hmac('sha256', $raw, 'test-secret');

        $this->call('POST', route('webhooks.verifyme.address'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_VERIFYME_SIGNATURE' => $sig,
        ], $raw)
            ->assertOk()
            ->assertJson(['status' => 'received']);
    }

    public function test_verifyme_webhook_rejects_with_invalid_signature(): void
    {
        SystemSetting::put('verifyme_webhook_secret', 'test-secret', 'security');

        $payload = [
            'data' => [
                'reference' => 'VMN_TEST_REF',
                'status' => ['status' => 'Completed'],
            ],
        ];

        $raw = json_encode($payload);

        $this->call('POST', route('webhooks.verifyme.address'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_VERIFYME_SIGNATURE' => 'invalid',
        ], $raw)
            ->assertStatus(403);
    }
}
