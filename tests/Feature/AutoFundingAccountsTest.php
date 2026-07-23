<?php

namespace Tests\Feature;

use App\Models\ApiCenter;
use App\Models\BankDetail;
use App\Models\User;
use App\Models\VerificationResult;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AutoFundingAccountsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_generates_and_persists_reserved_accounts()
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $user = User::create([
            'fullname' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@fuwa.ng',
            'password' => Hash::make('Password@123'),
            'number' => '08112233445',
            'email_verified_at' => now(),
        ]);

        if (! Schema::hasTable('verification_results')) {
            Schema::create('verification_results', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('service_type');
                $table->string('identifier');
                $table->string('provider_name');
                $table->json('response_data')->nullable();
                $table->string('status')->nullable();
                $table->string('reference_id')->nullable();
                $table->timestamps();
            });
        }

        VerificationResult::create([
            'user_id' => $user->id,
            'service_type' => 'bvn_verification',
            'identifier' => '22209291663',
            'provider_name' => 'Test',
            'response_data' => ['bvn' => '22209291663'],
            'status' => 'success',
            'reference_id' => 'BVN-TESTREF1',
        ]);

        ApiCenter::create([
            'payvessel_api_key' => 'pv_key',
            'payvessel_endpoint' => 'https://api.payvessel.com/api/external/request/customerReservedAccount/',
            'payvessel_businessid' => 'pv_biz',
            'monnify_api_key' => 'mn_key',
            'monnify_secret_key' => 'mn_secret',
            'monnify_contract_code' => 'mn_contract',
            'monnify_endpoint_auth' => 'https://api.monnify.com/api/v1/auth/login',
            'monnify_endpoint_reserve' => 'https://api.monnify.com/api/v2/bank-transfer/reserved-accounts',
        ]);

        Http::fake([
            'https://api.monnify.com/api/v1/auth/login' => Http::response([
                'requestSuccessful' => true,
                'responseBody' => [
                    'accessToken' => 'token',
                ],
            ], 200),
            'https://api.monnify.com/api/v2/bank-transfer/reserved-accounts' => Http::response([
                'requestSuccessful' => true,
                'responseBody' => [
                    'accountName' => 'Test User',
                    'accounts' => [
                        ['bankName' => 'Wema Bank', 'accountNumber' => '1000000001'],
                        ['bankName' => 'GTBank', 'accountNumber' => '1000000002'],
                    ],
                ],
            ], 200),
            'https://api.payvessel.com/api/external/request/customerReservedAccount/' => Http::response([
                'data' => [
                    'accountNumber' => '2000000001',
                    'accountName' => 'Test User',
                ],
            ], 200),
        ]);

        $res = $this->actingAs($user)->postJson(route('payment.auto_funding.ensure'));

        $res->assertOk()
            ->assertJson([
                'status' => true,
            ]);

        $this->assertDatabaseHas('bank_details', [
            'email' => 'test@fuwa.ng',
            'Wema_account' => '1000000001',
            'GTBank_account' => '1000000002',
            'psb9' => '2000000001',
        ]);

        $detail = BankDetail::where('email', 'test@fuwa.ng')->first();
        $this->assertNotNull($detail);
        $this->assertNotEmpty($detail->account_reference);
    }

    /** @test */
    public function regenerate_is_forbidden_for_non_admin_users()
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $user = User::create([
            'fullname' => 'Regular User',
            'username' => 'regular',
            'email' => 'regular@fuwa.ng',
            'password' => Hash::make('Password@123'),
            'number' => '08112233445',
            'role' => 'user',
        ]);

        $res = $this->actingAs($user)->postJson(route('payment.auto_funding.regenerate'));
        $res->assertStatus(403);
    }
}
