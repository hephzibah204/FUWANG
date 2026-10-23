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
        SystemSetting::set('ipe_clearance_mode', 'manual');
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

    public function test_user_can_submit_ipe_clearance_with_only_tracking_id_and_category(): void
    {
        $user = $this->makeUser(1500.00);

        $response = $this->actingAs($user)->postJson(route('services.clearance.verify'), [
            'mode' => 'submit',
            'category' => 'New Enrollment for ID Retrieval',
            'number' => 'BTX947E60001020',
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
        $this->assertEquals('BTX947E60001020', $record->identifier);
        $this->assertArrayNotHasKey('nin', $record->response_data);

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
            'https://robosttech.com/api/clearance' => function (\Illuminate\Http\Client\Request $request) {
                $data = $request->data();
                $this->assertEquals('BTX-ROBO-999', $data['tracking_id']);
                $this->assertArrayNotHasKey('nin', $data);
                return Http::response([
                    'status' => 'success',
                    'message' => 'Request queued for processing',
                ], 200);
            },
        ]);

        $user = $this->makeUser(1000.00);

        $response = $this->actingAs($user)->postJson(route('services.clearance.verify'), [
            'mode' => 'submit',
            'category' => 'Improcessing Error',
            'number' => 'BTX-ROBO-999',
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

        $record = VerificationResult::where('identifier', 'BTX-ROBO-999')->first();
        $this->assertArrayNotHasKey('nin', $record->response_data);

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

    public function test_standard_browser_submission_redirects_to_html_clearance_report(): void
    {
        $user = $this->makeUser(1500.00);

        // Regular browser POST (not postJson)
        $response = $this->actingAs($user)->post(route('services.clearance.verify'), [
            'mode' => 'submit',
            'category' => 'Improcessing Error',
            'number' => 'BTX-BROWSER-TEST-123',
        ]);

        $record = VerificationResult::where('identifier', 'BTX-BROWSER-TEST-123')->first();
        $this->assertNotNull($record);

        // Must redirect to the HTML clearance report
        $response->assertRedirect(route('services.clearance.report', $record->id));

        // Visit the report page and verify HTML rendering
        $reportResponse = $this->actingAs($user)->get(route('services.clearance.report', $record->id));
        $reportResponse->assertOk();
        $reportResponse->assertSee('NIMC IPE Clearance Certificate');
        $reportResponse->assertSee('BTX-BROWSER-TEST-123');
        $reportResponse->assertSee($record->reference_id);
        
        // Assert no provider names are leaked in the HTML
        $content = $reportResponse->getContent();
        $this->assertStringNotContainsStringIgnoringCase('Robosttech', $content);
        $this->assertStringNotContainsStringIgnoringCase('Dataverify', $content);
        $this->assertStringNotContainsStringIgnoringCase('Vuvaa', $content);
    }

    public function test_browser_status_check_redirects_to_clearance_report(): void
    {
        $user = $this->makeUser(500.00);

        $record = VerificationResult::create([
            'user_id' => $user->id,
            'service_type' => 'ipe_clearance',
            'identifier' => 'BTX-EXISTING-CHECK',
            'provider_name' => 'ADMIN_MANUAL',
            'status' => 'successful',
            'reference_id' => 'CLR-CHECK-777',
            'response_data' => [
                'category' => 'Improcessing Error',
                'new_tracking_id' => 'NEW-TID-777888',
                'amount_paid' => 700.00,
            ],
        ]);

        $response = $this->actingAs($user)->post(route('services.clearance.verify'), [
            'mode' => 'status',
            'number' => 'BTX-EXISTING-CHECK',
        ]);

        $response->assertRedirect(route('services.clearance.report', $record->id));

        $reportResponse = $this->actingAs($user)->get(route('services.clearance.report', $record->id));
        $reportResponse->assertOk();
        $reportResponse->assertSee('NEW-TID-777888');
        $reportResponse->assertSee('Clearance Completed Successfully');
    }

    public function test_extract_new_tracking_id_from_various_payload_structures(): void
    {
        // 1. Direct key
        $this->assertEquals('NEW-TID-001', VerificationResult::extractNewTrackingId(['new_tracking_id' => 'NEW-TID-001'], 'ORIG-001'));
        $this->assertEquals('NEW-TID-002', VerificationResult::extractNewTrackingId(['newTrackingID' => 'NEW-TID-002'], 'ORIG-001'));

        // 2. Nested under data or api_response
        $this->assertEquals('NEW-TID-003', VerificationResult::extractNewTrackingId([
            'data' => ['new_tracking_id' => 'NEW-TID-003']
        ], 'ORIG-001'));

        $this->assertEquals('NEW-TID-004', VerificationResult::extractNewTrackingId([
            'api_response' => ['data' => ['newTrackingID' => 'NEW-TID-004']]
        ], 'ORIG-001'));

        // 3. Extracted from remarks/note string
        $this->assertEquals('BTX9988776655', VerificationResult::extractNewTrackingId('Clearance resolved. New Tracking ID: BTX9988776655', 'ORIG-001'));

        // 4. Ignores original tracking ID
        $this->assertNull(VerificationResult::extractNewTrackingId(['tracking_id' => 'ORIG-001'], 'ORIG-001'));
    }

    public function test_ipe_report_displays_proper_messaging_when_cleared_directly_without_new_tracking_id(): void
    {
        $user = $this->makeUser(500.00);

        // Clearance record that is successful but has NO new tracking ID (direct clearance on original ID)
        $record = VerificationResult::create([
            'user_id' => $user->id,
            'service_type' => 'ipe_clearance',
            'identifier' => 'BTX-ORIGINAL-DIRECT',
            'provider_name' => 'ADMIN_MANUAL',
            'status' => 'successful',
            'reference_id' => 'CLR-DIRECT-111',
            'admin_note' => 'Original Tracking ID cleared directly on NIMC portal.',
            'response_data' => [
                'category' => 'Improcessing Error',
                'amount_paid' => 700.00,
            ],
        ]);

        $reportResponse = $this->actingAs($user)->get(route('services.clearance.report', $record->id));
        $reportResponse->assertOk();
        $reportResponse->assertSee('BTX-ORIGINAL-DIRECT');
        $reportResponse->assertSee('Clearance Completed Successfully');
        $reportResponse->assertSee('Cleared NIMC Tracking ID (Unlocked)');
        $reportResponse->assertSee('Direct Clearance on Original Tracking ID');
        $reportResponse->assertSee('Not Required');
    }

    public function test_status_query_retrieves_nested_new_tracking_id_from_dataverify_api(): void
    {
        $user = $this->makeUser(500.00);
        SystemSetting::set('ipe_clearance_mode', 'dataverify');

        Http::fake([
            'https://dataverify.org/api/developers/ipe_status.php' => Http::response([
                'status' => true,
                'request_status' => 'completed',
                'trackingID' => 'BTX-DV-SUBMITTED',
                'data' => [
                    'newTrackingID' => 'BTX-NEW-FROM-DATAVERIFY',
                ],
                'message' => 'IPE clearance finalized successfully.',
            ], 200),
        ]);

        $existing = VerificationResult::create([
            'user_id' => $user->id,
            'service_type' => 'ipe_clearance',
            'identifier' => 'BTX-DV-SUBMITTED',
            'provider_name' => 'DataVerify',
            'status' => 'waiting_for_review',
            'reference_id' => 'CLR-DV-999',
            'response_data' => [
                'category' => 'Improcessing Error',
                'amount_paid' => 700.00,
            ],
        ]);

        $response = $this->actingAs($user)->postJson(route('services.clearance.verify'), [
            'mode' => 'status',
            'number' => 'BTX-DV-SUBMITTED',
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => true,
            'data' => [
                'status' => 'successful',
                'new_tracking_id' => 'BTX-NEW-FROM-DATAVERIFY',
            ]
        ]);

        $existing->refresh();
        $this->assertEquals('successful', $existing->status);
        $this->assertEquals('BTX-NEW-FROM-DATAVERIFY', $existing->resolved_new_tracking_id);
    }

    public function test_ipe_clearance_submits_via_dataverify_api(): void
    {
        SystemSetting::set('ipe_clearance_mode', 'dataverify');
        SystemSetting::set('dataverify_api_key', 'dv-test-key-777');

        Http::fake([
            'https://dataverify.org/api/developers/ipe.php' => Http::response([
                'status' => true,
                'message' => 'IPE clearance request submitted successfully to DataVerify.',
                'transaction_id' => 'DV-TX-888',
                'trackingID' => 'BTX-DV-NEW-01',
            ], 200),
        ]);

        $user = $this->makeUser(1000.00);

        $response = $this->actingAs($user)->postJson(route('services.clearance.verify'), [
            'mode' => 'submit',
            'number' => 'BTX-DV-NEW-01',
            'category' => 'Biometric Exception',
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => true,
            'provider' => 'DataVerify',
            'data' => [
                'tracking_id' => 'BTX-DV-NEW-01',
                'status' => 'waiting_for_review',
            ],
        ]);

        $this->assertDatabaseHas('verification_results', [
            'identifier' => 'BTX-DV-NEW-01',
            'provider_name' => 'DataVerify',
            'status' => 'waiting_for_review',
        ]);

        $balance = AccountBalance::where('user_id', $user->id)->value('user_balance');
        $this->assertEquals(300.00, (float) $balance);
    }

    public function test_ipe_clearance_auto_mode_fails_over_from_robosttech_to_dataverify(): void
    {
        SystemSetting::set('ipe_clearance_mode', 'auto');
        SystemSetting::set('dataverify_api_key', 'dv-key-active');

        ApiCenter::create([
            'robosttech_api_key' => 'robost-key-123',
            'robosttech_endpoint_clearance' => 'https://robosttech.com/api/clearance',
            'dataverify_api_key' => 'dv-key-active',
        ]);

        Http::fake([
            'https://robosttech.com/api/clearance' => Http::response([
                'status' => 'failed',
                'message' => 'Provider gateway timeout or maintenance',
            ], 500),
            'https://dataverify.org/api/developers/ipe.php' => Http::response([
                'status' => true,
                'message' => 'Processed via DataVerify fallback',
                'transaction_id' => 'DV-TX-FALLBACK-01',
            ], 200),
        ]);

        $user = $this->makeUser(1000.00);

        $response = $this->actingAs($user)->postJson(route('services.clearance.verify'), [
            'mode' => 'submit',
            'number' => 'BTX-FAILOVER-01',
            'category' => 'Improcessing Error',
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => true,
            'provider' => 'DataVerify',
            'data' => [
                'tracking_id' => 'BTX-FAILOVER-01',
                'status' => 'waiting_for_review',
            ],
        ]);

        $this->assertDatabaseHas('verification_results', [
            'identifier' => 'BTX-FAILOVER-01',
            'provider_name' => 'DataVerify',
            'status' => 'waiting_for_review',
        ]);
    }
}


