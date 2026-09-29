<?php

namespace Tests\Feature;

use App\Models\AccountBalance;
use App\Models\ApiCenter;
use App\Models\BvnRetrievalRequest;
use App\Models\CustomApi;
use App\Models\FeatureToggle;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\VerificationResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BvnRetrievalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        FeatureToggle::updateOrCreate(
            ['feature_name' => 'bvn_verification'],
            ['is_active' => true]
        );

        ApiCenter::create([
            'dataverify_api_key' => 'test-api-key',
            'dataverify_endpoint_bvn_retrieval' => 'https://dataverify.com.ng/api/developers/bvn_retrieval.php',
            'dataverify_endpoint_bvn_retrieval_status' => 'https://dataverify.com.ng/api/developers/bvn_retrieval_status.php',
        ]);

        SystemSetting::set('bvn_retrieval_price', 800);
    }

    public function test_user_can_view_bvn_page_with_retrieval_tab(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('services.bvn'));

        $response->assertStatus(200);
        $response->assertSee('BVN Retrieval');
        $response->assertSee('Lost or Forgotten BVN Retrieval');
        $response->assertSee('800');
    }

    public function test_user_can_submit_bvn_retrieval_and_wallet_is_debited(): void
    {
        $user = User::factory()->create();
        AccountBalance::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'user_balance' => 2000.00,
        ]);

        Http::fake([
            'https://dataverify.com.ng/api/developers/bvn_retrieval.php' => Http::response([
                'status' => 'success',
                'transaction_id' => 'DV-TX-998877',
                'message' => 'BVN Retrieval request received and queued.',
            ], 200),
        ]);

        $response = $this->actingAs($user)->post(route('services.bvn.retrieve'), [
            'phone' => '08012345678',
            'full_name' => 'Musa Babatunde Okon',
            'dob' => '15-05-1990',
        ]);

        $response->assertRedirect(route('services.bvn'));
        $response->assertSessionHas('status');

        // Verify balance debited by 800
        $balance = AccountBalance::where('user_id', $user->id)->first();
        $this->assertEquals(1200.00, (float) $balance->user_balance);

        // Verify BvnRetrievalRequest record created
        $this->assertDatabaseHas('bvn_retrieval_requests', [
            'user_id' => $user->id,
            'phone_number' => '08012345678',
            'full_name' => 'Musa Babatunde Okon',
            'provider_transaction_id' => 'DV-TX-998877',
            'status' => 'pending',
            'amount' => 800.00,
        ]);
    }

    public function test_submission_failure_triggers_automatic_refund(): void
    {
        $user = User::factory()->create();
        AccountBalance::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'user_balance' => 2000.00,
        ]);

        Http::fake([
            'https://dataverify.com.ng/api/developers/bvn_retrieval.php' => Http::response([
                'status' => 'error',
                'message' => 'Service temporarily unavailable',
            ], 500),
        ]);

        $response = $this->actingAs($user)->post(route('services.bvn.retrieve'), [
            'phone' => '08012345678',
            'full_name' => 'Musa Babatunde Okon',
        ]);

        // Failed submission should refund user
        $balance = AccountBalance::where('user_id', $user->id)->first();
        $this->assertEquals(2000.00, (float) $balance->user_balance);

        // No pending retrieval request should be left
        $this->assertDatabaseMissing('bvn_retrieval_requests', [
            'phone_number' => '08012345678',
        ]);
    }

    public function test_check_retrieval_status_completes_and_saves_to_vault(): void
    {
        $user = User::factory()->create();
        AccountBalance::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'user_balance' => 1000.00,
        ]);

        $request = BvnRetrievalRequest::create([
            'user_id' => $user->id,
            'phone_number' => '08012345678',
            'full_name' => 'Musa Babatunde Okon',
            'transaction_id' => 'BVNRET-TEST-001',
            'provider_transaction_id' => 'DV-TX-998877',
            'provider' => 'dataverify',
            'amount' => 800.00,
            'status' => 'pending',
        ]);

        Http::fake([
            'https://dataverify.com.ng/api/developers/bvn_retrieval_status.php' => Http::response([
                'status' => 'completed',
                'message' => 'BVN found',
                'data' => [
                    'status' => 'completed',
                    'bvn' => '22114455667',
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)->post(route('services.bvn.retrieve.check', $request->id));

        $response->assertRedirect(route('services.bvn'));
        $request->refresh();

        $this->assertEquals('completed', $request->status);
        $this->assertEquals('22114455667', $request->retrieved_bvn);
        $this->assertNotNull($request->completed_at);

        // Check vault record was created
        $this->assertDatabaseHas('verification_results', [
            'user_id' => $user->id,
            'service_type' => 'bvn_retrieval',
            'identifier' => '22114455667',
            'status' => 'success',
        ]);
    }

    public function test_check_retrieval_status_not_found_refunds_wallet(): void
    {
        $user = User::factory()->create();
        AccountBalance::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'user_balance' => 1000.00,
        ]);

        $request = BvnRetrievalRequest::create([
            'user_id' => $user->id,
            'phone_number' => '08099999999',
            'full_name' => 'Unknown Person',
            'transaction_id' => 'BVNRET-TEST-002',
            'provider_transaction_id' => 'DV-TX-NOTFOUND',
            'provider' => 'dataverify',
            'amount' => 800.00,
            'status' => 'pending',
        ]);

        Http::fake([
            'https://dataverify.com.ng/api/developers/bvn_retrieval_status.php' => Http::response([
                'status' => 'not_found',
                'message' => 'No BVN record found for the provided details',
            ], 200),
        ]);

        $response = $this->actingAs($user)->post(route('services.bvn.retrieve.check', $request->id));

        $response->assertRedirect(route('services.bvn'));
        $request->refresh();

        $this->assertEquals('refunded', $request->status);
        $this->assertNotNull($request->refunded_at);

        // Wallet was credited back 800
        $balance = AccountBalance::where('user_id', $user->id)->first();
        $this->assertEquals(1800.00, (float) $balance->user_balance);
    }

    public function test_poll_bvn_retrievals_command(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        AccountBalance::create([
            'user_id' => $user1->id,
            'email' => $user1->email,
            'user_balance' => 500.00,
        ]);

        AccountBalance::create([
            'user_id' => $user2->id,
            'email' => $user2->email,
            'user_balance' => 500.00,
        ]);

        $req1 = BvnRetrievalRequest::create([
            'user_id' => $user1->id,
            'phone_number' => '08011111111',
            'full_name' => 'Alice User',
            'transaction_id' => 'BVNRET-CMD-001',
            'provider_transaction_id' => 'DV-CMD-001',
            'provider' => 'dataverify',
            'amount' => 800.00,
            'status' => 'pending',
        ]);

        $req2 = BvnRetrievalRequest::create([
            'user_id' => $user2->id,
            'phone_number' => '08022222222',
            'full_name' => 'Bob User',
            'transaction_id' => 'BVNRET-CMD-002',
            'provider_transaction_id' => 'DV-CMD-002',
            'provider' => 'dataverify',
            'amount' => 800.00,
            'status' => 'pending',
        ]);

        Http::fake([
            'https://dataverify.com.ng/api/developers/bvn_retrieval_status.php' => function ($request) {
                $data = $request->data();
                if (($data['transaction_id'] ?? '') === 'DV-CMD-001') {
                    return Http::response([
                        'status' => 'completed',
                        'data' => ['bvn' => '22998877665'],
                    ], 200);
                }

                if (($data['transaction_id'] ?? '') === 'DV-CMD-002') {
                    return Http::response([
                        'status' => 'not_found',
                        'message' => 'Record not found',
                    ], 200);
                }

                return Http::response(['status' => 'pending'], 200);
            },
        ]);

        $this->artisan('bvn:poll-retrievals')
            ->expectsOutputToContain('Polling DataVerify')
            ->assertSuccessful();

        $req1->refresh();
        $this->assertEquals('completed', $req1->status);
        $this->assertEquals('22998877665', $req1->retrieved_bvn);

        $req2->refresh();
        $this->assertEquals('refunded', $req2->status);

        // Bob was refunded 800 -> 500 + 800 = 1300
        $balance2 = AccountBalance::where('user_id', $user2->id)->first();
        $this->assertEquals(1300.00, (float) $balance2->user_balance);
    }
}
