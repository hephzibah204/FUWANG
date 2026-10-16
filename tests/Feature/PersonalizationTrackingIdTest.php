<?php

namespace Tests\Feature;

use App\Models\AccountBalance;
use App\Models\ApiCenter;
use App\Models\FeatureToggle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PersonalizationTrackingIdTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        FeatureToggle::query()->updateOrCreate(
            ['feature_name' => 'identity_verification'],
            ['is_active' => true]
        );
    }

    public function test_personalization_accepts_tracking_id_and_posts_tracking_id_payload(): void
    {
        $user = User::create([
            'fullname' => 'Test User',
            'username' => 'u_pers_001',
            'email' => 'pers@example.com',
            'password' => Hash::make('Password@123'),
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        AccountBalance::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'user_balance' => 100000,
            'api_key' => 'user',
        ]);

        ApiCenter::create([
            'robosttech_api_key' => 'robost_key',
            'robosttech_endpoint_personalization' => 'https://robosttech.com/api',
        ]);

        Http::fake(function ($request) {
            if (str_ends_with($request->url(), '/personalization')) {
                $data = $request->data();
                if (($data['tracking_id'] ?? null) !== 'ABC12345XYZ' || ($data['number'] ?? null) !== 'ABC12345XYZ') {
                    return Http::response(['message' => 'Bad payload'], 422);
                }
                return Http::response(['status' => 'success', 'message' => 'OK'], 200);
            }
            return Http::response(['message' => 'Unexpected request'], 500);
        });

        $response = $this->actingAs($user)
            ->post(route('services.personalization.verify'), ['tracking_id' => 'ABC12345XYZ'])
            ->assertOk()
            ->assertJsonPath('status', true);

        $reportUrl = $response->json('report_url');
        $this->assertNotEmpty($reportUrl);

        // Verify the HTML report renders beautifully with no raw JSON or vendor names
        $reportResponse = $this->actingAs($user)->get($reportUrl);
        $reportResponse->assertOk()
            ->assertSee('ABC12345XYZ')
            ->assertSee('NIMC Identity Personalization Certificate')
            ->assertDontSee('Robosttech')
            ->assertDontSee('Dataverify')
            ->assertDontSee('<pre class="text-white">', false);

        // Verify unauthorized user cannot view report
        $otherUser = User::create([
            'fullname' => 'Other User',
            'username' => 'u_pers_other',
            'email' => 'other@example.com',
            'password' => Hash::make('Password@123'),
        ]);
        $this->actingAs($otherUser)->get($reportUrl)->assertForbidden();

        // Verify transaction history show page works seamlessly for Personalization (no VTU crash)
        $tx = \App\Models\Transaction::where('order_type', 'Personalization')->latest()->firstOrFail();
        $historyResp = $this->actingAs($user)->get(route('history.show', $tx->transaction_id));
        $historyResp->assertOk()
            ->assertSee($tx->transaction_id)
            ->assertSee('ABC12345XYZ')
            ->assertSee('View / Print Official Report');
    }

    public function test_personalization_pending_submission_and_status_polling_returns_printable_nin_slip(): void
    {
        $user = User::create([
            'fullname' => 'Adewale Johnson',
            'username' => 'u_pers_async',
            'email' => 'async@example.com',
            'password' => Hash::make('Password@123'),
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        $balance = AccountBalance::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'user_balance' => 5000,
            'api_key' => 'user',
        ]);

        ApiCenter::create([
            'robosttech_api_key' => 'robost_key_123',
            'robosttech_endpoint_personalization' => 'https://robosttech.com/api',
        ]);

        $dummyPdfBase64 = base64_encode('%PDF-1.4 %Dummy PDF binary representation for test slip generation');

        Http::fake([
            'https://robosttech.com/api/personalization' => Http::response([
                'status' => 'pending',
                'request_status' => 'pending',
                'message' => 'Ticket accepted for processing',
                'tracking_id' => 'TRK99887766',
            ], 200),
            'https://robosttech.com/api/personalization_status' => Http::response([
                'status' => 'completed',
                'request_status' => 'completed',
                'message' => 'Personalization completed successfully',
                'nin' => '12345678901',
                'user_data' => [
                    'firstname' => 'Adewale',
                    'lastname' => 'Johnson',
                    'middlename' => 'Kola',
                    'birthdate' => '1992-05-14',
                    'gender' => 'Male',
                    'nin' => '12345678901',
                ],
                'pdf_base64' => $dummyPdfBase64,
            ], 200),
        ]);

        // 1. Initial submission: Provider returns pending, user charged, record is pending
        $submitResp = $this->actingAs($user)
            ->post(route('services.personalization.verify'), ['tracking_id' => 'TRK99887766'])
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('is_pending', true)
            ->assertJsonPath('request_status', 'pending');

        $resultId = $submitResp->json('result_id');
        $this->assertNotNull($resultId);

        $freshBalance = $balance->fresh()->user_balance;
        $this->assertEquals(4900, $freshBalance, 'User must be charged on submission per provider policy');

        // 2. Poll status endpoint: mode=status (must NOT debit wallet again)
        $pollResp = $this->actingAs($user)
            ->post(route('services.personalization.verify'), [
                'mode' => 'status',
                'tracking_id' => 'TRK99887766',
                'result_id' => $resultId,
            ])
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('is_completed', true)
            ->assertJsonPath('request_status', 'completed')
            ->assertJsonPath('nin', '12345678901');

        $this->assertEquals(4900, $balance->fresh()->user_balance, 'Status check must not debit wallet balance');

        // 3. Verify slip endpoints are available
        $regularSlipUrl = route('services.nin.slip', ['id' => $resultId, 'type' => 'regular_slip', 'print' => 1]);
        $premiumSlipUrl = route('services.nin.slip', ['id' => $resultId, 'type' => 'premium_slip', 'print' => 1]);

        $regularSlipResp = $this->actingAs($user)->get($regularSlipUrl);
        $regularSlipResp->assertOk()
            ->assertSee('1234 567 8901')
            ->assertSee('REGULAR NIN SLIP');

        $premiumSlipResp = $this->actingAs($user)->get($premiumSlipUrl);
        $premiumSlipResp->assertOk()
            ->assertSee('1234 567 8901');

        // 4. Verify HTML report displays printable NIN slip with NIN and print links
        $reportUrl = route('services.personalization.report', $resultId);
        $reportResp = $this->actingAs($user)->get($reportUrl);
        $reportResp->assertOk()
            ->assertSee('1234 567 8901')
            ->assertSee('TRK99887766')
            ->assertSee('Print Regular Slip')
            ->assertSee('Print Premium ID Plastic Slip')
            ->assertSee('Download PDF Slip');

        // 5. Verify PDF download returns valid binary PDF and is never empty
        $downloadPdfUrl = route('services.nin.slip', ['id' => $resultId, 'type' => 'regular_slip', 'download' => 1]);
        $pdfResp = $this->actingAs($user)->get($downloadPdfUrl);
        $pdfResp->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
        $this->assertNotEmpty($pdfResp->getContent());
        $this->assertStringStartsWith('%PDF', $pdfResp->getContent());

        $downloadReportPdfUrl = route('services.personalization.report', ['id' => $resultId, 'download' => 1]);
        $reportPdfResp = $this->actingAs($user)->get($downloadReportPdfUrl);
        $reportPdfResp->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
        $this->assertNotEmpty($reportPdfResp->getContent());
    }

    public function test_personalization_report_auto_syncs_with_gateway_when_accessed_while_pending(): void
    {
        $user = User::create([
            'fullname' => 'Fatima Aliyu',
            'username' => 'u_pers_sync',
            'email' => 'fatima@example.com',
            'password' => Hash::make('Password@123'),
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        ApiCenter::create([
            'robosttech_api_key' => 'robost_key_sync',
            'robosttech_endpoint_personalization' => 'https://robosttech.com/api',
        ]);

        $dummyPdf = base64_encode('%PDF-1.4 %Personalization slip test binary for auto sync verification');

        // Create a result that was initially saved as pending
        $result = \App\Models\VerificationResult::create([
            'user_id' => $user->id,
            'service_type' => 'personalization',
            'identifier' => 'TRKSYNC7788',
            'provider_name' => 'Robosttech',
            'status' => 'pending',
            'reference_id' => 'VR-PERS-SYNC-01',
            'admin_note' => 'Personalization in progress.',
            'response_data' => [
                'status' => 'pending',
                'request_status' => 'pending',
                'tracking_id' => 'TRKSYNC7788',
            ],
        ]);

        Http::fake([
            'https://robosttech.com/api/personalization_status' => Http::response([
                'status' => true,
                'request_status' => 'completed',
                'message' => 'Identity personalization successful',
                'nin' => '98765432109',
                'user_data' => [
                    'firstname' => 'Fatima',
                    'lastname' => 'Aliyu',
                    'birthdate' => '1995-10-20',
                    'gender' => 'Female',
                    'nin' => '98765432109',
                ],
                'pdf_base64' => $dummyPdf,
            ], 200),
        ]);

        // Access the report page directly while record is pending in DB
        $reportUrl = route('services.personalization.report', $result->id);
        $resp = $this->actingAs($user)->get($reportUrl);
        $resp->assertOk()
            ->assertSee('9876 543 2109')
            ->assertSee('Fatima')
            ->assertSee('Aliyu');

        // Verify the database record was automatically synchronized and updated to successful
        $fresh = $result->fresh();
        $this->assertEquals('successful', $fresh->status);
        $this->assertEquals('98765432109', $fresh->response_data['nin']);
        $this->assertNotEmpty($fresh->response_data['pdf_base64']);

        // Verify PDF download delivers the ready-to-print PDF
        $downloadResp = $this->actingAs($user)->get(route('services.personalization.report', ['id' => $result->id, 'download' => 1]));
        $downloadResp->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $downloadResp->getContent());
    }
}

