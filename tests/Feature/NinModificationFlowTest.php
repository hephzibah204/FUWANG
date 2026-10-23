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

    public function test_user_can_accept_modification_consent_without_being_charged(): void
    {
        $user = $this->createUser();
        $user->forceFill(['nin_mod_consent' => false, 'nin_mod_consent_at' => null])->save();

        $this->actingAs($user)->postJson(route('services.nin.modification.consent'))
            ->assertOk()->assertJson(['status' => true]);

        $user->refresh();
        $this->assertTrue($user->nin_mod_consent);
        $this->assertNotNull($user->nin_mod_consent_at);
        $this->assertSame(5000.0, $user->wallet_balance);
    }

    public function test_submission_reaches_admin_queue_without_profile_consent_columns(): void
    {
        $user = $this->createUser();
        \Illuminate\Support\Facades\Schema::table('users', function ($table) {
            $table->dropColumn(['nin_mod_consent', 'nin_mod_consent_at']);
        });
        $user = User::findOrFail($user->id);

        $this->actingAs($user)->postJson(route('services.nin.modification.consent'))
            ->assertOk()->assertJson(['status' => true]);

        $response = $this->postJson(route('services.nin.modification.submit'), [
            'self_service' => 'no',
            'nin' => '12345678901',
            'full_name' => 'John Applicant',
            'modification_type' => 'name',
            'old_value' => 'Old Name',
            'new_value' => 'New Name',
            'photo' => UploadedFile::fake()->create('affidavit.pdf', 10, 'application/pdf'),
        ])->assertOk()->assertJson(['status' => true]);

        $record = VerificationResult::findOrFail($response->json('result_id'));
        $this->assertSame('waiting_for_review', $record->status);
        $this->assertTrue($record->response_data['consent_accepted']);
        $this->assertNotEmpty($record->response_data['transaction_id']);
        $this->assertSame(2500.0, $user->fresh()->wallet_balance);
        $this->actingAs($this->createAdmin(), 'admin')
            ->get(route('admin.verifications.nin_modifications.index'))
            ->assertOk()->assertSee('12345678901');
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

    public function test_user_sees_modification_form_with_visible_submit_button_and_fields(): void
    {
        $user = $this->createUser(5000.00);

        $response = $this->actingAs($user)->get(route('services.nin.modification'));

        $response->assertOk()
            ->assertSee('Submit Modification Request')
            ->assertSee('id="submitBtn"', false)
            ->assertSee('Select Modification Type')
            ->assertSee('Current Detail on Record (Old)')
            ->assertSee('Requested Update (New)')
            ->assertSee('Supporting Photo or Document');
    }

    public function test_user_can_submit_nin_modification_with_pdf_document(): void
    {
        $user = $this->createUser(5000.00);

        $pdfFile = UploadedFile::fake()->create('affidavit_change_of_name.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($user)->postJson(route('services.nin.modification.submit'), [
            'self_service' => 'no',
            'nin' => '98765432109',
            'full_name' => 'John Applicant',
            'modification_type' => 'dob',
            'old_value' => '1992-05-14',
            'new_value' => '1995-10-20',
            'photo' => $pdfFile,
            'consent' => '1',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', true);

        $record = VerificationResult::where('identifier', '98765432109')->firstOrFail();
        $this->assertEquals('waiting_for_review', $record->status);
        $this->assertEquals('application/pdf', $record->response_data['document_mime']);
        $this->assertEquals('affidavit_change_of_name.pdf', $record->response_data['document_name']);
    }

    public function test_admin_verifications_index_links_to_nin_modifications_review(): void
    {
        $user = $this->createUser(5000.00);
        $file = UploadedFile::fake()->image('court_affidavit.jpg');

        $this->actingAs($user)->postJson(route('services.nin.modification.submit'), [
            'self_service' => 'no',
            'nin' => '11223344556',
            'full_name' => 'Mariam Bello',
            'modification_type' => 'name',
            'old_value' => 'Mariam Bello',
            'new_value' => 'Mariam Abubakar',
            'photo' => $file,
        ]);

        $record = VerificationResult::where('identifier', '11223344556')->firstOrFail();
        $admin = $this->createAdmin();

        $vaultResp = $this->actingAs($admin, 'admin')->get(route('admin.verifications.index'));
        $vaultResp->assertOk()
            ->assertSee(route('admin.verifications.nin_modifications.index'))
            ->assertSee(route('admin.verifications.nin_modifications.show', $record->id));
    }
}
