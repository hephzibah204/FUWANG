<?php

namespace Tests\Feature;

use App\Models\AccountBalance;
use App\Models\ApiCenter;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\VerificationPrice;
use App\Models\VerificationResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IpeClearanceManualFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        VerificationPrice::create([
            'ipe_clearance_price' => 700.00,
        ]);
    }

    private function makeUser(float $initialBalance = 1500.00): User
    {
        $user = User::create([
            'fullname' => 'John Doe',
            'username' => 'testuser_' . uniqid(),
            'email' => 'user_' . uniqid() . '@example.com',
            'password' => Hash::make('Secret123!'),
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        AccountBalance::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'user_balance' => $initialBalance,
            'api_key' => 'user',
        ]);

        return $user;
    }

    public function test_user_can_submit_ipe_clearance_with_nin_and_category(): void
    {
        $user = $this->makeUser(1500.00);

        $response = $this->actingAs($user)->postJson(route('services.clearance.verify'), [
            'mode' => 'submit',
            'category' => 'New Enrollment for ID Retrieval',
            'number' => 'BTX947E60001020',
            'nin' => '11223344556',
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => true,
            'data' => [
                'tracking_id' => 'BTX947E60001020',
                'category' => 'New Enrollment for ID Retrieval',
                'status' => 'waiting_for_review',
            ],
        ]);

        $this->assertDatabaseHas('verification_results', [
            'identifier' => 'BTX947E60001020',
            'service_type' => 'ipe_clearance',
            'provider_name' => 'ADMIN_MANUAL',
            'status' => 'waiting_for_review',
        ]);

        $record = VerificationResult::where('identifier', 'BTX947E60001020')->first();
        $this->assertEquals('11223344556', $record->response_data['nin']);

        // User balance should have been debited 700: 1500 - 700 = 800
        $balance = AccountBalance::where('user_id', $user->id)->value('user_balance');
        $this->assertEquals(800.00, (float) $balance);
    }

    public function test_admin_marking_successful_assigns_new_tracking_id(): void
    {
        $user = $this->makeUser(800.00);
        $admin = $this->makeUser(0.00);

        $record = VerificationResult::create([
            'user_id' => $user->id,
            'service_type' => 'ipe_clearance',
            'identifier' => 'BTX947E60001020',
            'provider_name' => 'ADMIN_MANUAL',
            'status' => 'waiting_for_review',
            'reference_id' => 'CLR-TEST-SUCCESS',
            'response_data' => [
                'category' => 'Improcessing Error',
                'amount_paid' => 700.00,
            ],
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.verifications.ipe_clearance.update', $record->id), [
            'status' => 'successful',
            'new_tracking_id' => 'NEW-TRACKING-9988',
            'nin' => '99887766554',
            'admin_note' => 'Issue cleared by NIMC desk.',
        ]);

        $response->assertRedirect();
        $record->refresh();
        $this->assertEquals('successful', $record->status);
        $this->assertEquals('NEW-TRACKING-9988', $record->response_data['new_tracking_id']);
        $this->assertEquals('99887766554', $record->response_data['nin']);

        // Balance remains unchanged (no refund on success)
        $balance = AccountBalance::where('user_id', $user->id)->value('user_balance');
        $this->assertEquals(800.00, (float) $balance);
    }

    public function test_admin_marking_failed_triggers_refund(): void
    {
        $user = $this->makeUser(800.00);
        $admin = $this->makeUser(0.00);

        $record = VerificationResult::create([
            'user_id' => $user->id,
            'service_type' => 'ipe_clearance',
            'identifier' => 'BTX-FAIL-001',
            'provider_name' => 'ADMIN_MANUAL',
            'status' => 'waiting_for_review',
            'reference_id' => 'CLR-TEST-FAIL',
            'response_data' => [
                'amount_paid' => 700.00,
            ],
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.verifications.ipe_clearance.update', $record->id), [
            'status' => 'failed',
            'admin_note' => 'Invalid biometric trace provided.',
        ]);

        $response->assertRedirect();
        $record->refresh();
        $this->assertEquals('failed', $record->status);

        // Refunded 700: 800 + 700 = 1500
        $balance = AccountBalance::where('user_id', $user->id)->value('user_balance');
        $this->assertEquals(1500.00, (float) $balance);
    }

    public function test_admin_can_export_and_import_bulk_results_csv(): void
    {
        $user = $this->makeUser(800.00);
        $admin = $this->makeUser(0.00);

        $record = VerificationResult::create([
            'user_id' => $user->id,
            'service_type' => 'ipe_clearance',
            'identifier' => 'BTX-BULK-123',
            'provider_name' => 'ADMIN_MANUAL',
            'status' => 'waiting_for_review',
            'reference_id' => 'CLR-BULK-001',
            'response_data' => [
                'amount_paid' => 700.00,
            ],
        ]);

        // Export CSV test
        $exportResponse = $this->actingAs($admin, 'admin')->get(route('admin.verifications.ipe_clearance.export'));
        $exportResponse->assertOk();

        // Import CSV test (mark successful with new tracking ID)
        $csvContent = "Reference ID,Status,New Tracking ID,NIN,Admin Note\n"
                    . "CLR-BULK-001,successful,BTX-RESOLVED-888,12345678901,Cleared in bulk\n";

        $file = UploadedFile::fake()->createWithContent('results.csv', $csvContent);

        $importResponse = $this->actingAs($admin, 'admin')->post(route('admin.verifications.ipe_clearance.import'), [
            'file' => $file,
        ]);

        $importResponse->assertRedirect();
        $record->refresh();
        $this->assertEquals('successful', $record->status);
        $this->assertEquals('BTX-RESOLVED-888', $record->response_data['new_tracking_id']);
        $this->assertEquals('12345678901', $record->response_data['nin']);
        $this->assertEquals('Cleared in bulk', $record->admin_note);
    }

    public function test_admin_can_switch_mode_between_manual_and_robosttech(): void
    {
        $admin = $this->makeUser(0.00);

        // Initial default mode is manual
        $this->assertEquals('manual', SystemSetting::get('ipe_clearance_mode', 'manual'));

        // Admin switches to Robosttech API
        $res = $this->actingAs($admin, 'admin')->post(route('admin.verifications.ipe_clearance.mode'), [
            'mode' => 'robosttech',
        ]);
        $res->assertRedirect();
        $this->assertEquals('robosttech', SystemSetting::get('ipe_clearance_mode'));

        // Admin switches back to Manual
        $res2 = $this->actingAs($admin, 'admin')->post(route('admin.verifications.ipe_clearance.mode'), [
            'mode' => 'manual',
        ]);
        $res2->assertRedirect();
        $this->assertEquals('manual', SystemSetting::get('ipe_clearance_mode'));
    }

    public function test_robosttech_mode_submits_via_api(): void
    {
        SystemSetting::set('ipe_clearance_mode', 'robosttech');
        ApiCenter::create([
            'robosttech_api_key' => 'test-api-key-123',
            'robosttech_endpoint_clearance' => 'https://robosttech.com/api/clearance',
            'robosttech_endpoint_clearance_status' => 'https://robosttech.com/api/clearance_status',
        ]);

        Http::fake([
            'https://robosttech.com/api/clearance' => Http::response([
                'status' => 'success',
                'message' => 'Request queued for processing',
            ], 200),
        ]);

        $user = $this->makeUser(1000.00);

        $response = $this->actingAs($user)->postJson(route('services.clearance.verify'), [
            'mode' => 'submit',
            'category' => 'Improcessing Error',
            'number' => 'BTX-ROBO-999',
            'nin' => '55443322110',
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => true,
            'data' => [
                'tracking_id' => 'BTX-ROBO-999',
                'status' => 'waiting_for_review',
            ],
        ]);

        $this->assertDatabaseHas('verification_results', [
            'identifier' => 'BTX-ROBO-999',
            'provider_name' => 'Robosttech',
            'status' => 'waiting_for_review',
        ]);

        // Balance debited 700: 1000 - 700 = 300
        $balance = AccountBalance::where('user_id', $user->id)->value('user_balance');
        $this->assertEquals(300.00, (float) $balance);
    }

    public function test_admin_can_sync_request_from_robosttech_api(): void
    {
        $admin = $this->makeUser(0.00);
        $user = $this->makeUser(300.00);

        ApiCenter::create([
            'robosttech_api_key' => 'test-api-key-123',
            'robosttech_endpoint_clearance_status' => 'https://robosttech.com/api/clearance_status',
        ]);

        Http::fake([
            'https://robosttech.com/api/clearance_status' => Http::response([
                'status' => 'successful',
                'new_tracking_id' => 'BTX-NEW-RESOLVED-111',
                'message' => 'Cleared successfully via provider',
            ], 200),
        ]);

        $record = VerificationResult::create([
            'user_id' => $user->id,
            'service_type' => 'ipe_clearance',
            'identifier' => 'BTX-SYNC-TEST',
            'provider_name' => 'Robosttech',
            'status' => 'waiting_for_review',
            'reference_id' => 'CLR-SYNC-001',
            'response_data' => [
                'amount_paid' => 700.00,
            ],
        ]);

        $res = $this->actingAs($admin, 'admin')->post(route('admin.verifications.ipe_clearance.sync_robosttech', $record->id));
        $res->assertRedirect();

        $record->refresh();
        $this->assertEquals('successful', $record->status);
        $this->assertEquals('BTX-NEW-RESOLVED-111', $record->response_data['new_tracking_id']);
    }

    public function test_robosttech_status_check_updates_status_and_awards_new_tracking_id(): void
    {
        SystemSetting::set('ipe_clearance_mode', 'robosttech');
        ApiCenter::create([
            'robosttech_api_key' => 'test-api-key-123',
            'robosttech_endpoint_clearance_status' => 'https://robosttech.com/api/clearance_status',
        ]);

        Http::fake([
            'https://robosttech.com/api/clearance_status' => Http::response([
                'status' => 'successful',
                'new_tracking_id' => 'BTX-RESOLVED-LIVE-555',
                'message' => 'Cleared successfully by provider',
            ], 200),
        ]);

        $user = $this->makeUser(300.00);

        $record = VerificationResult::create([
            'user_id' => $user->id,
            'service_type' => 'ipe_clearance',
            'identifier' => 'BTX-STATUS-LOOKUP',
            'provider_name' => 'Robosttech',
            'status' => 'waiting_for_review',
            'reference_id' => 'CLR-STATUS-001',
            'response_data' => [
                'amount_paid' => 700.00,
            ],
        ]);

        $response = $this->actingAs($user)->postJson(route('services.clearance.verify'), [
            'mode' => 'status',
            'number' => 'BTX-STATUS-LOOKUP',
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => true,
            'data' => [
                'tracking_id' => 'BTX-STATUS-LOOKUP',
                'status' => 'successful',
                'new_tracking_id' => 'BTX-RESOLVED-LIVE-555',
            ],
        ]);

        $record->refresh();
        $this->assertEquals('successful', $record->status);
        $this->assertEquals('BTX-RESOLVED-LIVE-555', $record->response_data['new_tracking_id']);
    }
}
