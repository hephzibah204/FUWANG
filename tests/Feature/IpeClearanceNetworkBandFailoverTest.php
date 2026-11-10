<?php

namespace Tests\Feature;

use App\Models\CustomApi;
use App\Models\User;
use App\Models\VerificationPrice;
use App\Models\VerificationResult;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IpeClearanceNetworkBandFailoverTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private CustomApi $dvProvider;
    private CustomApi $robostProvider;

    protected function setUp(): void
    {
        parent::setUp();

        \App\Models\SystemSetting::set('ipe_clearance_mode', 'auto');

        VerificationPrice::create([
            'ipe_clearance_price' => 700,
        ]);

        $this->user = User::create([
            'fullname' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@fuwa.ng',
            'password' => Hash::make('Password@123'),
            'number' => '08012345678',
            'email_verified_at' => now(),
        ]);

        app(WalletService::class)->credit($this->user, 5000, 'Test funding', 'TEST-CREDIT-1');

        $this->dvProvider = CustomApi::create([
            'name' => 'DataVerify Clearance Gateway',
            'provider_identifier' => 'dataverify',
            'service_type' => 'ipe_clearance',
            'status' => true,
            'api_key' => 'dv-secret-test-key',
            'endpoint' => 'https://api.dataverify.org/api/v1/ipe',
            'priority' => 1,
        ]);

        $this->robostProvider = CustomApi::create([
            'name' => 'Robosttech Processing Node',
            'provider_identifier' => 'robosttech',
            'service_type' => 'ipe_clearance',
            'status' => true,
            'api_key' => 'robost-secret-test-key',
            'endpoint' => 'https://robosttech.com/api/v1/clearance',
            'priority' => 2,
        ]);
    }

    #[Test]
    public function test_clearance_index_renders_network_band_options_and_masks_vendor_names(): void
    {
        $response = $this->actingAs($this->user)->get(route('services.clearance'));

        $response->assertOk();
        $response->assertSee('Select Network Band');
        $response->assertSee('✨ Smart Routing &amp; Automatic Failover (Recommended)', false);
        $response->assertSee('Band 1');
        $response->assertSee('Band 2');
        $response->assertSee('Smart Failover Protected');
        $response->assertSee('Network Band');

        // Verify vendor names are masked from user-facing visible text
        $content = $response->getContent();
        $this->assertStringNotContainsString('DataVerify Clearance Gateway', $content);
        $this->assertStringNotContainsString('Robosttech Processing Node', $content);
    }

    #[Test]
    public function test_ipe_clearance_submission_routes_to_band_1_dataverify(): void
    {
        Http::fake([
            '*dataverify.org*' => Http::response([
                'status' => 'success',
                'success' => true,
                'message' => 'Clearance processed successfully',
                'data' => [
                    'trackingID' => 'BTX947E60001020',
                    'new_tracking_id' => 'NEWTID999888',
                ],
            ], 200),
            '*robosttech.com*' => Http::response([
                'status' => 'error',
                'message' => 'Should not be called',
            ], 500),
        ]);

        $response = $this->actingAs($this->user)->postJson(route('services.clearance.verify'), [
            'mode' => 'submit',
            'category' => 'Improcessing Error',
            'number' => 'BTX947E60001020',
            'network_band' => 'band_1',
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => true,
        ]);

        $result = VerificationResult::where('identifier', 'BTX947E60001020')->first();
        $this->assertNotNull($result);
        $this->assertEquals('NEWTID999888', $result->resolved_new_tracking_id);
        $this->assertEquals('Band 1', $result->response_data['network_band']);
        $this->assertFalse($result->response_data['smart_failover'] ?? false);
    }

    #[Test]
    public function test_ipe_clearance_smart_failover_when_band_1_fails_automatically_falls_over_to_band_2(): void
    {
        Http::fake([
            // Primary Band 1 (DataVerify) fails with 500 error
            '*dataverify.org*' => Http::response([
                'status' => 'error',
                'message' => 'DataVerify gateway temporary gateway error',
            ], 500),
            // Fallback Band 2 (RobostTech) succeeds!
            '*robosttech.com*' => Http::response([
                'status' => 'cleared',
                'message' => 'Cleared successfully on RobostTech backup line',
                'tracking_id' => 'BTX947E60001020',
                'new_tracking_id' => 'ROBOSTNEW777888',
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->postJson(route('services.clearance.verify'), [
            'mode' => 'submit',
            'category' => 'Improcessing Error',
            'number' => 'BTX947E60001020',
            'network_band' => 'band_1', // requested Band 1, but it will fail over
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => true,
        ]);

        $result = VerificationResult::where('identifier', 'BTX947E60001020')->first();
        $this->assertNotNull($result);
        $this->assertEquals('ROBOSTNEW777888', $result->resolved_new_tracking_id);
        $this->assertEquals('Band 2', $result->response_data['network_band']);
        $this->assertTrue($result->response_data['smart_failover']);
        $this->assertEquals('Band 1', $result->response_data['primary_attempted']);
        $this->assertStringContainsString('Smart Failover to Band 2', $result->admin_note);
    }

    #[Test]
    public function test_ipe_clearance_routes_to_band_2_and_falls_over_to_band_1_if_band_2_fails(): void
    {
        Http::fake([
            // Primary Band 2 (RobostTech) fails
            '*robosttech.com*' => Http::response([
                'status' => 'error',
                'message' => 'Robosttech clearance service timed out',
            ], 500),
            // Fallback Band 1 (DataVerify) succeeds
            '*dataverify.org*' => Http::response([
                'status' => 'success',
                'success' => true,
                'message' => 'Cleared via DataVerify backup gateway',
                'data' => [
                    'trackingID' => 'BTX947E60001020',
                    'new_tracking_id' => 'DVNEW555444',
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->postJson(route('services.clearance.verify'), [
            'mode' => 'submit',
            'category' => 'Improcessing Error',
            'number' => 'BTX947E60001020',
            'network_band' => 'band_2', // requested Band 2, falls over to Band 1
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => true,
        ]);

        $result = VerificationResult::where('identifier', 'BTX947E60001020')->first();
        $this->assertNotNull($result);
        $this->assertEquals('DVNEW555444', $result->resolved_new_tracking_id);
        $this->assertEquals('Band 1', $result->response_data['network_band']);
        $this->assertTrue($result->response_data['smart_failover']);
        $this->assertEquals('Band 2', $result->response_data['primary_attempted']);
    }

    #[Test]
    public function test_ipe_clearance_smart_routing_default_auto_fails_over_seamlessly(): void
    {
        Http::fake([
            // Primary Band 1 (DataVerify) fails with gateway error
            '*dataverify.org*' => Http::response([
                'status' => 'error',
                'message' => 'DataVerify gateway node timeout',
            ], 500),
            // Backup Band 2 (RobostTech) succeeds!
            '*robosttech.com*' => Http::response([
                'status' => 'cleared',
                'message' => 'Cleared via backup node',
                'tracking_id' => 'BTX947E60001020',
                'new_tracking_id' => 'SMARTFAILOVERNEW1',
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->postJson(route('services.clearance.verify'), [
            'mode' => 'submit',
            'category' => 'Improcessing Error',
            'number' => 'BTX947E60001020',
            // No network_band passed => defaults to Smart Routing & Automatic Failover
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => true,
        ]);

        $result = VerificationResult::where('identifier', 'BTX947E60001020')->first();
        $this->assertNotNull($result);
        $this->assertEquals('SMARTFAILOVERNEW1', $result->resolved_new_tracking_id);
        $this->assertTrue($result->response_data['smart_failover'] ?? false);
    }

    #[Test]
    public function test_ipe_clearance_history_displays_network_band_badge(): void
    {
        VerificationResult::create([
            'user_id' => $this->user->id,
            'service_type' => 'ipe_clearance',
            'identifier' => 'BTX947E60001020',
            'provider_name' => 'DataVerify',
            'status' => 'successful',
            'reference_id' => 'CLR-TEST1234',
            'admin_note' => 'Cleared successfully.',
            'response_data' => [
                'network_band' => 'Band 1',
                'category' => 'Improcessing Error',
                'new_tracking_id' => 'NEWTID999111',
            ],
        ]);

        $response = $this->actingAs($this->user)->get(route('services.clearance'));

        $response->assertOk();
        $response->assertSee('BTX947E60001020');
        $response->assertSee('NEWTID999111');
        $response->assertSee('Band 1');
    }
}
