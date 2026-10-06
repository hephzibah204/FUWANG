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

class NinValidationManualFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        VerificationPrice::create([
            'validation_price' => 700.00,
        ]);
    }

    private function makeUser(float $initialBalance = 1500.00): User
    {
        $user = User::create([
            'fullname' => 'Alice Validator',
            'username' => 'val_user_' . uniqid(),
            'email' => 'val_' . uniqid() . '@example.com',
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

    public function test_user_can_submit_nin_validation_in_manual_mode(): void
    {
        SystemSetting::set('nin_validation_mode', 'manual');
        $user = $this->makeUser(1500.00);

        $response = $this->actingAs($user)->postJson(route('services.validation.verify'), [
            'mode' => 'submit',
            'number' => '11223344556',
            'validation_reason' => 'Bank KYC Verification',
            'remarks' => 'Please expedite vetting',
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => true,
            'provider' => 'ADMIN_MANUAL',
            'data' => [
                'nin' => '11223344556',
                'status' => 'waiting_for_review',
            ],
        ]);

        $this->assertDatabaseHas('verification_results', [
            'identifier' => '11223344556',
            'service_type' => 'validation',
            'provider_name' => 'ADMIN_MANUAL',
            'status' => 'waiting_for_review',
        ]);

        // Balance should be debited 700: 1500 - 700 = 800
        $balance = AccountBalance::where('user_id', $user->id)->value('user_balance');
        $this->assertEquals(800.00, (float) $balance);
    }

    public function test_admin_marking_validation_successful(): void
    {
        $user = $this->makeUser(800.00);
        $admin = $this->makeUser(0.00);

        $record = VerificationResult::create([
            'user_id' => $user->id,
            'service_type' => 'validation',
            'identifier' => '11223344556',
            'provider_name' => 'ADMIN_MANUAL',
            'status' => 'waiting_for_review',
            'reference_id' => 'VAL-TEST-SUCCESS',
            'response_data' => [
                'amount_paid' => 700.00,
                'nin' => '11223344556',
            ],
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.verifications.nin_validation.update', $record->id), [
            'status' => 'successful',
            'nin' => '11223344556',
            'admin_note' => 'Identity validated on NIMC portal successfully.',
        ]);

        $response->assertRedirect();
        $record->refresh();
        $this->assertEquals('successful', $record->status);
        $this->assertEquals('Identity validated on NIMC portal successfully.', $record->admin_note);

        // Balance remains unchanged (no refund on success)
        $balance = AccountBalance::where('user_id', $user->id)->value('user_balance');
        $this->assertEquals(800.00, (float) $balance);
    }

    public function test_admin_marking_validation_failed_triggers_refund(): void
    {
        $user = $this->makeUser(800.00);
        $admin = $this->makeUser(0.00);

        $record = VerificationResult::create([
            'user_id' => $user->id,
            'service_type' => 'validation',
            'identifier' => '99887766554',
            'provider_name' => 'ADMIN_MANUAL',
            'status' => 'waiting_for_review',
            'reference_id' => 'VAL-TEST-FAIL',
            'response_data' => [
                'amount_paid' => 700.00,
                'nin' => '99887766554',
            ],
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.verifications.nin_validation.update', $record->id), [
            'status' => 'failed',
            'nin' => '99887766554',
            'admin_note' => 'Record mismatch or invalid NIN.',
        ]);

        $response->assertRedirect();
        $record->refresh();
        $this->assertEquals('failed', $record->status);

        // Refunded 700: 800 + 700 = 1500
        $balance = AccountBalance::where('user_id', $user->id)->value('user_balance');
        $this->assertEquals(1500.00, (float) $balance);
    }

    public function test_admin_can_switch_mode_between_manual_and_robosttech(): void
    {
        $admin = $this->makeUser(0.00);

        $this->assertEquals('manual', SystemSetting::get('nin_validation_mode', 'manual'));

        // Switch to robosttech
        $res = $this->actingAs($admin, 'admin')->post(route('admin.verifications.nin_validation.mode'), [
            'mode' => 'robosttech',
        ]);
        $res->assertRedirect();
        $this->assertEquals('robosttech', SystemSetting::get('nin_validation_mode'));

        // Switch to manual
        $res2 = $this->actingAs($admin, 'admin')->post(route('admin.verifications.nin_validation.mode'), [
            'mode' => 'manual',
        ]);
        $res2->assertRedirect();
        $this->assertEquals('manual', SystemSetting::get('nin_validation_mode'));
    }

    public function test_robosttech_mode_submits_via_api(): void
    {
        SystemSetting::set('nin_validation_mode', 'robosttech');
        ApiCenter::create([
            'robosttech_api_key' => 'test-val-key',
            'robosttech_endpoint_validation' => 'https://robosttech.com/api/validation',
        ]);

        Http::fake([
            'https://robosttech.com/api/validation' => Http::response([
                'status' => 'success',
                'message' => 'Validation queued with provider',
            ], 200),
        ]);

        $user = $this->makeUser(1000.00);

        $response = $this->actingAs($user)->postJson(route('services.validation.verify'), [
            'mode' => 'submit',
            'number' => '12345678901',
            'validation_reason' => 'Corporate KYC',
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => true,
            'provider' => 'Robosttech',
            'data' => [
                'nin' => '12345678901',
                'status' => 'waiting_for_review',
            ],
        ]);

        $this->assertDatabaseHas('verification_results', [
            'identifier' => '12345678901',
            'provider_name' => 'Robosttech',
        ]);

        // Debited 700: 1000 - 700 = 300
        $balance = AccountBalance::where('user_id', $user->id)->value('user_balance');
        $this->assertEquals(300.00, (float) $balance);
    }

    public function test_admin_can_export_and_import_bulk_validation_results_csv(): void
    {
        $user = $this->makeUser(800.00);
        $admin = $this->makeUser(0.00);

        $record = VerificationResult::create([
            'user_id' => $user->id,
            'service_type' => 'validation',
            'identifier' => '55667788990',
            'provider_name' => 'ADMIN_MANUAL',
            'status' => 'waiting_for_review',
            'reference_id' => 'VAL-BULK-001',
            'response_data' => [
                'amount_paid' => 700.00,
            ],
        ]);

        // Export CSV test
        $exportResponse = $this->actingAs($admin, 'admin')->get(route('admin.verifications.nin_validation.export'));
        $exportResponse->assertOk();

        // Import CSV test (mark validated)
        $csvContent = "Reference ID,Status,Admin Note\n"
                    . "VAL-BULK-001,successful,Validated via bulk NIMC upload\n";

        $file = UploadedFile::fake()->createWithContent('val_results.csv', $csvContent);

        $importResponse = $this->actingAs($admin, 'admin')->post(route('admin.verifications.nin_validation.import'), [
            'file' => $file,
        ]);

        $importResponse->assertRedirect();
        $record->refresh();
        $this->assertEquals('successful', $record->status);
        $this->assertEquals('Validated via bulk NIMC upload', $record->admin_note);
    }
}
