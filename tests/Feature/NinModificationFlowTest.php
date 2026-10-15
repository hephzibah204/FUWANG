<?php

namespace Tests\Feature;

use App\Models\AccountBalance;
use App\Models\Admin;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\VerificationResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NinModificationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        SystemSetting::set('nin_modification_price', 2500);
        Storage::fake('public');
    }

    private function createUser(float $balance = 5000.00): User
    {
        $user = User::create([
            'fullname' => 'John Applicant',
            'username' => 'applicant_' . uniqid(),
            'email' => 'applicant_' . uniqid() . '@example.com',
            'password' => Hash::make('Secret123!'),
            'nin_mod_consent' => true,
            'kyc_tier' => 1,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        AccountBalance::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'user_balance' => $balance,
            'api_key' => 'user',
        ]);

        return $user;
    }

    private function createAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Super Admin',
            'username' => 'admin_' . uniqid(),
            'email' => 'admin_' . uniqid() . '@fuwa.ng',
            'password' => Hash::make('Secret123!'),
            'is_super_admin' => true,
        ]);
    }

    public function test_user_can_submit_nin_modification_request(): void
    {
        $user = $this->createUser(5000.00);

        $file = UploadedFile::fake()->image('court_affidavit.jpg');

        $response = $this->actingAs($user)->postJson(route('services.nin.modification.submit'), [
            'self_service' => 'no',
            'nin' => '12345678901',
            'full_name' => 'John Applicant',
            'modification_type' => 'name',
            'old_value' => 'John A. Applicant',
            'new_value' => 'John B. Applicant',
            'photo' => $file,
        ]);

        $response->assertOk()->assertJsonPath('status', true);

        // Assert debited
        $this->assertEquals(2500.00, $user->fresh()->balance->user_balance);

        // Assert record created
        $this->assertDatabaseHas('verification_results', [
            'user_id' => $user->id,
            'service_type' => 'nin_modification',
            'identifier' => '12345678901',
            'status' => 'waiting_for_review',
        ]);
    }

    public function test_admin_can_view_and_process_modification_request(): void
    {
        $user = $this->createUser(5000.00);
        $file = UploadedFile::fake()->image('court_affidavit.jpg');

        $this->actingAs($user)->postJson(route('services.nin.modification.submit'), [
            'self_service' => 'no',
            'nin' => '12345678901',
            'full_name' => 'John Applicant',
            'modification_type' => 'name',
            'old_value' => 'Old Name',
            'new_value' => 'New Name',
            'photo' => $file,
        ]);

        $record = VerificationResult::where('service_type', 'nin_modification')->firstOrFail();

        $admin = $this->createAdmin();

        // Admin index
        $indexResp = $this->actingAs($admin, 'admin')->get(route('admin.verifications.nin_modifications.index'));
        $indexResp->assertOk()
            ->assertSee('John Applicant')
            ->assertSee('12345678901');

        // Admin show
        $showResp = $this->actingAs($admin, 'admin')->get(route('admin.verifications.nin_modifications.show', $record->id));
        $showResp->assertOk()
            ->assertSee('John Applicant')
            ->assertSee('Old Name')
            ->assertSee('New Name');

        // Admin marks as failed (triggers refund)
        $updateResp = $this->actingAs($admin, 'admin')->post(route('admin.verifications.nin_modifications.update', $record->id), [
            'status' => 'failed',
            'admin_note' => 'Supporting document unreadable',
        ]);

        $updateResp->assertRedirect();
        $this->assertEquals('failed', $record->fresh()->status);
        $this->assertEquals('Supporting document unreadable', $record->fresh()->admin_note);

        // Assert refunded back to 5000.00
        $this->assertEquals(5000.00, $user->fresh()->balance->user_balance);
    }
}
