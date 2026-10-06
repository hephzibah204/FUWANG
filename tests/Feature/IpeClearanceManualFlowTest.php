<?php

namespace Tests\Feature;

use App\Models\AccountBalance;
use App\Models\User;
use App\Models\VerificationPrice;
use App\Models\VerificationResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_user_can_submit_ipe_clearance_with_category_and_charged_700(): void
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

        // Assert record created in verification_results
        $this->assertDatabaseHas('verification_results', [
            'identifier' => 'BTX947E60001020',
            'service_type' => 'ipe_clearance',
            'provider_name' => 'ADMIN_MANUAL',
            'status' => 'waiting_for_review',
        ]);

        // User balance should have been debited 700: 1500 - 700 = 800
        $balance = AccountBalance::where('user_id', $user->id)->value('user_balance');
        $this->assertEquals(800.00, (float) $balance);
    }

    public function test_user_can_check_status_of_manual_clearance(): void
    {
        $user = $this->makeUser(1000.00);

        VerificationResult::create([
            'user_id' => $user->id,
            'service_type' => 'ipe_clearance',
            'identifier' => 'BTX947E60001020',
            'provider_name' => 'ADMIN_MANUAL',
            'status' => 'waiting_for_review',
            'reference_id' => 'CLR-TEST1',
            'admin_note' => 'Vetting in progress',
            'response_data' => [
                'category' => 'Improcessing Error',
            ],
        ]);

        $response = $this->actingAs($user)->postJson(route('services.clearance.verify'), [
            'mode' => 'status',
            'number' => 'BTX947E60001020',
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => true,
            'data' => [
                'tracking_id' => 'BTX947E60001020',
                'category' => 'Improcessing Error',
                'status' => 'waiting_for_review',
                'admin_note' => 'Vetting in progress',
            ],
        ]);

        // Balance must remain unchanged during status query
        $balance = AccountBalance::where('user_id', $user->id)->value('user_balance');
        $this->assertEquals(1000.00, (float) $balance);
    }

    public function test_admin_rejecting_clearance_refunds_700(): void
    {
        $user = $this->makeUser(800.00);
        $admin = $this->makeUser(0.00);

        $record = VerificationResult::create([
            'user_id' => $user->id,
            'service_type' => 'ipe_clearance',
            'identifier' => 'BTX947E60001020',
            'provider_name' => 'ADMIN_MANUAL',
            'status' => 'waiting_for_review',
            'reference_id' => 'CLR-REJECT-1',
            'response_data' => [
                'category' => 'Enrollment Is still Being Processed',
                'amount_paid' => 700.00,
            ],
        ]);

        // Rejection should refund 700 back to the user
        $response = $this->actingAs($admin, 'admin')->post(route('admin.verifications.ipe_clearance.update', $record->id), [
            'status' => 'failed',
            'admin_note' => 'Invalid or unrecognized tracking ID provided.',
        ]);

        $response->assertRedirect();
        $this->assertEquals('failed', $record->fresh()->status);
        $this->assertEquals('Invalid or unrecognized tracking ID provided.', $record->fresh()->admin_note);

        // Refunded 700 back to user balance: 800 + 700 = 1500
        $balance = AccountBalance::where('user_id', $user->id)->value('user_balance');
        $this->assertEquals(1500.00, (float) $balance);
    }
}
