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
            'ipe_clearance_price' => 400.00,
        ]);
    }

    private function makeUser(float $initialBalance = 1000.00): User
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

    public function test_user_can_submit_ipe_clearance_for_manual_vetting(): void
    {
        $user = $this->makeUser(1000.00);

        $response = $this->actingAs($user)->postJson(route('services.clearance.verify'), [
            'mode' => 'submit',
            'number' => 'TRK-987654321',
            'remarks' => 'Urgent vetting needed',
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => true,
        ]);

        // Assert record created in verification_results
        $this->assertDatabaseHas('verification_results', [
            'identifier' => 'TRK-987654321',
            'service_type' => 'ipe_clearance',
            'provider_name' => 'ADMIN_MANUAL',
            'status' => 'waiting_for_review',
        ]);

        // User balance should have been debited 400: 1000 - 400 = 600
        $balance = AccountBalance::where('user_id', $user->id)->value('user_balance');
        $this->assertEquals(600.00, (float) $balance);
    }

    public function test_user_can_check_status_of_manual_clearance(): void
    {
        $user = $this->makeUser(1000.00);

        VerificationResult::create([
            'user_id' => $user->id,
            'service_type' => 'ipe_clearance',
            'identifier' => 'TRK-STATUS-123',
            'provider_name' => 'ADMIN_MANUAL',
            'status' => 'waiting_for_review',
            'reference_id' => 'CLR-TEST1',
            'admin_note' => 'Under investigation',
        ]);

        $response = $this->actingAs($user)->postJson(route('services.clearance.verify'), [
            'mode' => 'status',
            'number' => 'TRK-STATUS-123',
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => true,
            'data' => [
                'tracking_id' => 'TRK-STATUS-123',
                'status' => 'waiting_for_review',
                'admin_note' => 'Under investigation',
            ],
        ]);

        // Balance must remain unchanged during status query
        $balance = AccountBalance::where('user_id', $user->id)->value('user_balance');
        $this->assertEquals(1000.00, (float) $balance);
    }

    public function test_admin_can_approve_or_reject_ipe_clearance(): void
    {
        $user = $this->makeUser(600.00);
        $admin = $this->makeUser(0.00);

        $record = VerificationResult::create([
            'user_id' => $user->id,
            'service_type' => 'ipe_clearance',
            'identifier' => 'TRK-REJECT-99',
            'provider_name' => 'ADMIN_MANUAL',
            'status' => 'waiting_for_review',
            'reference_id' => 'CLR-REJECT-1',
        ]);

        // Rejection should refund the user
        $response = $this->actingAs($admin, 'admin')->post(route('admin.verifications.ipe_clearance.update', $record->id), [
            'status' => 'failed',
            'admin_note' => 'Invalid biometric trace provided.',
        ]);

        $response->assertRedirect();
        $this->assertEquals('failed', $record->fresh()->status);
        $this->assertEquals('Invalid biometric trace provided.', $record->fresh()->admin_note);

        // Refunded 400 back to user balance: 600 + 400 = 1000
        $balance = AccountBalance::where('user_id', $user->id)->value('user_balance');
        $this->assertEquals(1000.00, (float) $balance);
    }
}
