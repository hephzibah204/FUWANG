<?php

namespace Tests\Feature;

use App\Models\AccountBalance;
use App\Models\ApiCenter;
use App\Models\CustomApi;
use App\Models\FeatureToggle;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\VerificationPrice;
use App\Models\VerificationResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RobosttechPostmanDocumentationAlignmentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        FeatureToggle::query()->updateOrCreate(
            ['feature_name' => 'identity_verification'],
            ['is_active' => true]
        );

        $this->user = User::create([
            'fullname' => 'Robost User',
            'username' => 'robost_user',
            'email' => 'robost.user@example.com',
            'password' => Hash::make('Secret123@!'),
            'api_key' => 'user_key_robost_token_123',
        ]);
        $this->user->forceFill(['email_verified_at' => now()])->save();

        AccountBalance::create([
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'user_balance' => 50000.00,
            'api_key' => 'user_key_robost_token_123',
        ]);

        VerificationPrice::updateOrCreate(['id' => 1], [
            'validation_price' => 700.00,
            'ipe_clearance_price' => 700.00,
            'personalization_price' => 100.00,
        ]);

        ApiCenter::create([
            'robosttech_api_key' => 'test_robost_api_key_6ebdc3cc',
            'robosttech_endpoint_nin' => 'https://robosttech.com/api',
            'robosttech_endpoint_validation' => 'https://robosttech.com/api',
            'robosttech_endpoint_clearance' => 'https://robosttech.com/api',
            'robosttech_endpoint_clearance_status' => 'https://robosttech.com/api/clearance_status',
            'robosttech_endpoint_personalization' => 'https://robosttech.com/api',
        ]);

        SystemSetting::set('nin_validation_mode', 'robosttech');
        SystemSetting::set('ipe_clearance_mode', 'robosttech');
    }

    /**
     * Postman Endpoint 1: POST /api/validation
     */
    public function test_postman_validation_submission(): void
    {
        Http::fake([
            'https://robosttech.com/api/validation' => function ($request) {
                $this->assertEquals('test_robost_api_key_6ebdc3cc', $request->header('api-key')[0] ?? null);
                $this->assertEquals('application/json', $request->header('Content-Type')[0] ?? null);
                $body = $request->data();
                $this->assertEquals('18855414402', $body['nin']);

                return Http::response([
                    'message' => 'Validation Submission Successfull',
                    'approved' => true,
                    'category' => 'new',
                    'success' => true,
                    'nin' => ' 18855414402',
                ], 200);
            },
        ]);

        $response = $this->actingAs($this->user)->postJson(route('services.validation.verify'), [
            'mode' => 'submit',
            'number' => '18855414402',
            'validation_reason' => 'Corporate KYC',
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => true,
            'provider' => 'Robosttech',
            'data' => [
                'nin' => '18855414402',
                'status' => 'waiting_for_review',
            ],
        ]);

        $this->assertDatabaseHas('verification_results', [
            'identifier' => '18855414402',
            'status' => 'waiting_for_review',
        ]);
    }

    /**
     * Postman Endpoint 2: POST /api/validation_status
     */
    public function test_postman_validation_status_in_progress_and_completed(): void
    {
        Http::fake([
            'https://robosttech.com/api/validation_status' => Http::sequence()
                ->push([
                    'message' => 'Uploaded',
                    'status' => 'sent',
                    'success' => false,
                    'in-progress' => true,
                ], 200)
                ->push([
                    'message' => 'Validation Successfull',
                    'approved' => true,
                    'status' => 'completed',
                    'success' => true,
                ], 200),
        ]);

        $record = VerificationResult::create([
            'user_id' => $this->user->id,
            'service_type' => 'validation',
            'identifier' => '18855414402',
            'provider_name' => 'Robosttech',
            'status' => 'waiting_for_review',
            'reference_id' => 'VAL-TEST-001',
            'response_data' => ['nin' => '18855414402', 'amount_paid' => 700.00],
        ]);

        // 1. In-progress check: returns in-progress: true, status: sent
        $response = $this->actingAs($this->user)->postJson(route('services.validation.verify'), [
            'mode' => 'status',
            'number' => '18855414402',
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => true,
            'data' => [
                'status' => 'waiting_for_review',
                'status_label' => 'Under Review / In Progress',
            ],
        ]);

        // 2. Completed check: returns success: true, status: completed
        $response2 = $this->actingAs($this->user)->postJson(route('services.validation.verify'), [
            'mode' => 'status',
            'number' => '18855414402',
        ]);

        $response2->assertOk();
        $response2->assertJson([
            'status' => true,
            'data' => [
                'status' => 'successful',
                'status_label' => 'Validated / Successful',
            ],
        ]);

        $record->refresh();
        $this->assertEquals('successful', $record->status);
    }

    /**
     * Postman Endpoint 3 & 4: POST /api/personalization & /api/personalization_status
     */
    public function test_postman_personalization_submission_and_status(): void
    {
        Http::fake([
            'https://robosttech.com/api/personalization' => function ($request) {
                $this->assertEquals('test_robost_api_key_6ebdc3cc', $request->header('api-key')[0] ?? null);
                $body = $request->data();
                $this->assertEquals('CKW49TGEN0001D4', $body['tracking_id']);

                return Http::response([
                    'message' => 'Personalization Submission Successfull',
                    'approved' => true,
                    'category' => 'to_get_slip',
                    'success' => true,
                    'tracking_id' => 'CKW49TGEN0001D4',
                ], 200);
            },
            'https://robosttech.com/api/personalization_status' => Http::response([
                'message' => 'Personalization Successfull',
                'personalized' => true,
                'success' => true,
                'status' => 'completed',
                'data' => [
                    'firstName' => 'JAMILA',
                    'lastName' => 'ABDULLAHI',
                    'dateOfBirth' => '01-01-1995',
                    'gender' => 'FEMALE',
                    'nin' => '35000000001',
                    'tracking_id' => 'CKW49TGEN0001D4',
                    'photo' => 'sample_base64_photo_data',
                ],
                'tracking_id' => 'CKW49TGEN0001D4',
                'reply' => 'Successfull',
            ], 200),
        ]);

        $subResponse = $this->actingAs($this->user)->postJson(route('services.personalization.verify'), [
            'tracking_id' => 'CKW49TGEN0001D4',
        ]);

        $subResponse->assertOk();
        $subResponse->assertJson([
            'status' => true,
            'is_pending' => true,
            'tracking_id' => 'CKW49TGEN0001D4',
        ]);

        $statusResponse = $this->actingAs($this->user)->postJson(route('services.personalization.verify'), [
            'mode' => 'status',
            'tracking_id' => 'CKW49TGEN0001D4',
        ]);

        $statusResponse->assertOk();
        $statusResponse->assertJson([
            'status' => true,
            'is_completed' => true,
            'nin' => '35000000001',
        ]);
    }

    /**
     * Postman Endpoint 5, 6, 7: NIN Verify, Phone, Demographic via Api\VerificationController
     */
    public function test_postman_nin_phone_and_demographic_api_calls(): void
    {
        $robostApi = CustomApi::create([
            'name' => 'RobostTech Global',
            'provider_identifier' => 'robosttech',
            'service_type' => 'nin_verification',
            'endpoint' => 'https://robosttech.com/api',
            'api_key' => 'test_robost_api_key_6ebdc3cc',
            'status' => true,
            'priority' => 1,
        ]);

        Http::fake([
            'https://robosttech.com/api/nin_verify' => function ($request) {
                $this->assertEquals('18855414402', $request->data()['nin']);
                return Http::response([
                    'message' => 'Verification Successfull',
                    'success' => true,
                    'data' => [
                        'nin' => '18855414402',
                        'firstname' => 'ALIYU',
                        'surname' => 'LAWAN',
                        'gender' => 'm',
                        'birthdate' => '27-11-2007',
                    ],
                ], 200);
            },
            'https://robosttech.com/api/nin_phone' => function ($request) {
                $this->assertEquals('08032364684', $request->data()['phone']);
                return Http::response([
                    'message' => 'Verification Successfull',
                    'success' => true,
                    'data' => [
                        'nin' => '13099075002',
                        'firstname' => 'WASILA',
                        'surname' => 'BALA',
                        'gender' => 'Female',
                    ],
                ], 200);
            },
            'https://robosttech.com/api/nin_demo' => function ($request) {
                $data = $request->data();
                $this->assertEquals('WASILA', $data['firstname']);
                $this->assertEquals('BALA', $data['lastname']);
                $this->assertEquals('2009-01-01', $data['dateOfBirth']);
                return Http::response([
                    'message' => 'Verification Successfull',
                    'success' => true,
                    'data' => [
                        'nin' => '13099075002',
                        'firstname' => 'WASILA',
                        'surname' => 'BALA',
                    ],
                ], 200);
            },
        ]);

        $plainToken = 'robost_user_plain_token_123';
        $this->user->update(['api_access_status' => 'approved']);
        \App\Models\ApiToken::create([
            'user_id' => $this->user->id,
            'name' => 'Default Test Token',
            'token_hash' => hash('sha256', $plainToken),
        ]);

        $headers = [
            'Authorization' => 'Bearer ' . $plainToken,
        ];

        // 1. Search by NIN: POST /api/nin_verify
        $res1 = $this->postJson('/api/v1/verifications/nin', [
            'mode' => 'nin',
            'number' => '18855414402',
            'api_provider_id' => $robostApi->id,
        ], $headers);
        $res1->assertOk()->assertJsonPath('status', true)->assertJsonPath('data.firstname', 'ALIYU');

        // 2. Search by Phone: POST /api/nin_phone
        $res2 = $this->postJson('/api/v1/verifications/nin', [
            'mode' => 'phone',
            'number' => '08032364684',
            'api_provider_id' => $robostApi->id,
        ], $headers);
        $res2->assertOk()->assertJsonPath('status', true)->assertJsonPath('data.firstname', 'WASILA');

        // 3. Search by Demographic: POST /api/nin_demo
        $res3 = $this->postJson('/api/v1/verifications/nin', [
            'mode' => 'demographic',
            'number' => 'WASILA_BALA',
            'firstname' => 'WASILA',
            'lastname' => 'BALA',
            'dob' => '2009-01-01',
            'gender' => 'female',
            'api_provider_id' => $robostApi->id,
        ], $headers);
        $res3->assertOk()->assertJsonPath('status', true)->assertJsonPath('data.firstname', 'WASILA');
    }

    /**
     * Postman Endpoint 8 & 9: IPE Clearance Request and Status Check with 'reply' Tracking ID
     */
    public function test_postman_ipe_clearance_submission_duplicate_handling_and_reply_tracking_id(): void
    {
        Http::fake([
            'https://robosttech.com/api/clearance' => function ($request) {
                $body = $request->data();
                if (($body['tracking_id'] ?? '') === '0RQ6C5ASWFS36LS') {
                    return Http::response([
                        'success' => false,
                        'tracking_id' => '0RQ6C5ASWFS36LS',
                        'message' => 'Already Exist',
                        'exist' => true,
                    ], 400);
                }

                return Http::response([
                    'message' => 'Clearance Submission Successfull',
                    'success' => true,
                    'tracking_id' => $body['tracking_id'] ?? '0RQ6C5ASWFS36LR',
                ], 200);
            },
            'https://robosttech.com/api/clearance_status' => Http::response([
                'message' => 'Clearance Successfull',
                'cleared' => true,
                'success' => true,
                'status' => 'completed',
                'tracking_id' => '0RQ6C5ASWFS36LU',
                'reply' => '2GVZ0SI8KO000VK',
            ], 200),
        ]);

        // 1. Success submission: POST /api/clearance
        $subRes = $this->actingAs($this->user)->postJson(route('services.clearance.verify'), [
            'mode' => 'submit',
            'category' => 'Improcessing Error',
            'number' => '0RQ6C5ASWFS36LR',
        ]);
        $subRes->assertOk()->assertJsonPath('status', true);

        // 2. Duplicate submission handled gracefully (Postman Request 8: Already Exist, exist: true)
        $dupRes = $this->actingAs($this->user)->postJson(route('services.clearance.verify'), [
            'mode' => 'submit',
            'category' => 'Improcessing Error',
            'number' => '0RQ6C5ASWFS36LS',
        ]);
        $dupRes->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.status', 'waiting_for_review');

        // 3. Status check returning 'reply' as new tracking ID (Postman Request 10: CHECK IPE STATUS SUCCESS)
        $record = VerificationResult::create([
            'user_id' => $this->user->id,
            'service_type' => 'ipe_clearance',
            'identifier' => '0RQ6C5ASWFS36LU',
            'provider_name' => 'Robosttech',
            'status' => 'waiting_for_review',
            'reference_id' => 'CLR-ROBOST-POSTMAN-001',
            'response_data' => ['tracking_id' => '0RQ6C5ASWFS36LU', 'amount_paid' => 700.00],
        ]);

        $statusRes = $this->actingAs($this->user)->postJson(route('services.clearance.verify'), [
            'mode' => 'status',
            'number' => '0RQ6C5ASWFS36LU',
        ]);

        $statusRes->assertOk();
        $statusRes->assertJson([
            'status' => true,
            'data' => [
                'status' => 'successful',
                'new_tracking_id' => '2GVZ0SI8KO000VK',
            ],
        ]);

        $record->refresh();
        $this->assertEquals('successful', $record->status);
        $this->assertEquals('2GVZ0SI8KO000VK', $record->response_data['new_tracking_id']);
    }
}
