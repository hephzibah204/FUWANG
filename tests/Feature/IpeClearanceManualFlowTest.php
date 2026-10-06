<?php

namespace Tests\Feature;

use App\Models\AccountBalance;
use App\Models\User;
use App\Models\VerificationPrice;
use App\Models\VerificationResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
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
}
