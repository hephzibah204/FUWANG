<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWebhookSecuritySettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_security_settings_endpoints(): void
    {
        $this->post(route('admin.settings.security.verifyme_ips'), [
            'verifyme_webhook_ips' => '127.0.0.1',
        ])->assertRedirect(route('admin.login'));
    }

    public function test_non_superadmin_cannot_modify_security_when_multiple_admins_exist(): void
    {
        Admin::create([
            'username' => 'a1',
            'email' => 'a1@example.com',
            'password' => 'Password@123',
            'role' => 'admin',
        ]);

        $admin = Admin::create([
            'username' => 'a2',
            'email' => 'a2@example.com',
            'password' => 'Password@123',
            'role' => 'admin',
        ]);

        $this->actingAs($admin, 'admin')
            ->postJson(route('admin.settings.security.verifyme_ips'), [
                'verifyme_webhook_ips' => '127.0.0.1',
            ])
            ->assertStatus(403);
    }

    public function test_superadmin_can_update_verifyme_ip_allowlist_with_validation(): void
    {
        $admin = Admin::create([
            'username' => 'sa',
            'email' => 'sa@example.com',
            'password' => 'Password@123',
            'role' => 'superadmin',
        ]);

        $this->actingAs($admin, 'admin')
            ->postJson(route('admin.settings.security.verifyme_ips'), [
                'verifyme_webhook_ips' => "127.0.0.1\n2001:db8::1",
            ])
            ->assertOk()
            ->assertJson(['status' => true]);

        $this->assertSame('127.0.0.1, 2001:db8::1', SystemSetting::get('verifyme_webhook_ips'));

        $this->actingAs($admin, 'admin')
            ->postJson(route('admin.settings.security.verifyme_ips'), [
                'verifyme_webhook_ips' => '999.999.1.1',
            ])
            ->assertStatus(422);
    }

    public function test_secret_rotation_invalidates_old_signatures(): void
    {
        $admin = Admin::create([
            'username' => 'sa',
            'email' => 'sa@example.com',
            'password' => 'Password@123',
            'role' => 'superadmin',
        ]);

        SystemSetting::put('verifyme_webhook_secret', 'old-secret', 'security');

        $payload = [
            'data' => [
                'reference' => 'VMN_TEST_REF',
                'status' => ['status' => 'Completed'],
            ],
        ];
        $raw = json_encode($payload);

        $oldSig = hash_hmac('sha256', $raw, 'old-secret');
        $this->call('POST', route('webhooks.verifyme.address'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_VERIFYME_SIGNATURE' => $oldSig,
        ], $raw)->assertOk();

        $this->actingAs($admin, 'admin')
            ->postJson(route('admin.settings.security.verifyme_secret'), [
                'verifyme_webhook_secret' => 'new-secret-value-123456',
            ])
            ->assertOk()
            ->assertJson(['status' => true]);

        $this->call('POST', route('webhooks.verifyme.address'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_VERIFYME_SIGNATURE' => $oldSig,
        ], $raw)->assertStatus(403);

        $newSig = hash_hmac('sha256', $raw, 'new-secret-value-123456');
        $this->call('POST', route('webhooks.verifyme.address'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_VERIFYME_SIGNATURE' => $newSig,
        ], $raw)->assertOk();
    }

    public function test_high_volume_webhook_processing_is_stable(): void
    {
        SystemSetting::put('verifyme_webhook_secret', 'perf-secret', 'security');

        $payload = [
            'data' => [
                'reference' => 'VMN_TEST_REF',
                'status' => ['status' => 'Completed'],
            ],
        ];
        $raw = json_encode($payload);
        $sig = hash_hmac('sha256', $raw, 'perf-secret');

        for ($i = 0; $i < 200; $i++) {
            $this->call('POST', route('webhooks.verifyme.address'), [], [], [], [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_VERIFYME_SIGNATURE' => $sig,
            ], $raw)->assertOk();
        }
    }
}

