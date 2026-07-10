<?php

namespace Tests\Feature;

use App\Models\ApiCenter;
use App\Models\User;
use App\Models\VerificationResult;
use App\Models\VirtualAccount;
use App\Services\VirtualAccounts\VirtualAccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VirtualAccountServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(): User
    {
        return User::create([
            'fullname' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password',
            'number' => '08012345678',
        ]);
    }

    private function seedApiCenter(): void
    {
        ApiCenter::create([
            'payvessel_api_key' => 'pv_key',
            'payvessel_secret_key' => 'pv_secret',
            'payvessel_endpoint' => 'https://payvessel.test/reserve',
            'monnify_api_key' => 'mn_key',
            'monnify_secret_key' => 'mn_secret',
            'monnify_contract_code' => 'mn_contract',
            'paystack_secret_key' => 'ps_secret',
            'flutterwave_secret_key' => 'flw_secret',
            'paypoint_api_key' => 'pp_key',
            'paypoint_secret_key' => 'pp_secret',
            'paypoint_businessid' => 'pp_bid',
            'paypoint_endpoint' => 'https://paypoint.test/reserve',
        ]);
    }

    private function seedSuccessfulBvn(User $user, string $bvn = '22209291663'): void
    {
        VerificationResult::create([
            'user_id' => $user->id,
            'service_type' => 'bvn_verification',
            'identifier' => $bvn,
            'provider_name' => 'Test',
            'response_data' => ['bvn' => $bvn],
            'status' => 'success',
            'reference_id' => 'BVN-TESTREF',
        ]);
    }

    public function test_it_creates_up_to_four_virtual_accounts_across_gateways(): void
    {
        $this->seedApiCenter();
        $user = $this->makeUser();
        $this->seedSuccessfulBvn($user);

        Http::fake([
            'https://payvessel.test/reserve' => Http::response([
                'data' => ['accountNumber' => '9000000001', 'accountName' => 'Test User'],
            ], 200),
            'https://api.monnify.com/api/v1/auth/login' => Http::response([
                'responseBody' => ['accessToken' => 'token'],
            ], 200),
            'https://api.monnify.com/api/v2/bank-transfer/reserved-accounts' => Http::response([
                'responseBody' => [
                    'status' => 'ACTIVE',
                    'accountName' => 'Test User',
                    'accounts' => [
                        ['bankName' => 'Wema Bank', 'accountNumber' => '9000000002'],
                    ],
                ],
            ], 200),
            'https://paypoint.test/reserve' => Http::response([
                'status' => 'success',
                'bankAccounts' => [['accountNumber' => '9000000003']],
            ], 200),
            'https://api.paystack.co/customer' => Http::response([
                'status' => true,
                'data' => ['customer_code' => 'CUS_xxx'],
            ], 200),
            'https://api.paystack.co/dedicated_account' => Http::response([
                'status' => true,
                'data' => [
                    'account_number' => '9000000004',
                    'account_name' => 'TEST USER',
                    'bank' => ['name' => 'Wema Bank'],
                    'id' => 123,
                ],
            ], 200),
            'https://api.flutterwave.com/v3/virtual-account-numbers' => Http::response([
                'status' => 'success',
                'message' => 'Virtual account created',
                'data' => ['account_number' => '9000000005', 'bank_name' => 'Flutterwave Bank'],
            ], 200),
        ]);

        $res = app(VirtualAccountService::class)->ensureAccounts($user);
        $this->assertTrue($res['status']);
        $this->assertCount(4, $res['accounts']);
        $this->assertSame(4, VirtualAccount::query()->where('user_id', $user->id)->whereIn('status', ['active', 'pending'])->count());
    }

    public function test_it_keeps_creating_other_accounts_when_one_gateway_fails(): void
    {
        $this->seedApiCenter();
        $user = $this->makeUser();
        $this->seedSuccessfulBvn($user);

        Http::fake([
            'https://payvessel.test/reserve' => Http::response(['message' => 'error'], 500),
            'https://api.monnify.com/api/v1/auth/login' => Http::response([
                'responseBody' => ['accessToken' => 'token'],
            ], 200),
            'https://api.monnify.com/api/v2/bank-transfer/reserved-accounts' => Http::response([
                'responseBody' => [
                    'status' => 'ACTIVE',
                    'accountName' => 'Test User',
                    'accounts' => [
                        ['bankName' => 'Wema Bank', 'accountNumber' => '9100000002'],
                    ],
                ],
            ], 200),
            'https://paypoint.test/reserve' => Http::response([
                'status' => 'success',
                'bankAccounts' => [['accountNumber' => '9100000003']],
            ], 200),
            'https://api.paystack.co/customer' => Http::response([
                'status' => true,
                'data' => ['customer_code' => 'CUS_xxx'],
            ], 200),
            'https://api.paystack.co/dedicated_account' => Http::response([
                'status' => true,
                'data' => [
                    'account_number' => '9100000004',
                    'account_name' => 'TEST USER',
                    'bank' => ['name' => 'Wema Bank'],
                    'id' => 123,
                ],
            ], 200),
            'https://api.flutterwave.com/v3/virtual-account-numbers' => Http::response([
                'status' => 'success',
                'message' => 'Virtual account created',
                'data' => ['account_number' => '9100000005', 'bank_name' => 'Flutterwave Bank'],
            ], 200),
        ]);

        $res = app(VirtualAccountService::class)->ensureAccounts($user);

        $this->assertTrue($res['status']);
        $this->assertCount(4, $res['accounts']);
        $this->assertFalse($res['providers']['payvessel']['ok']);
        $gateways = VirtualAccount::query()->where('user_id', $user->id)->pluck('gateway')->all();
        $this->assertContains('monnify', $gateways);
        $this->assertContains('palmpay', $gateways);
        $this->assertContains('paystack', $gateways);
        $this->assertContains('flutterwave', $gateways);
    }

    public function test_it_sends_nin_when_latest_kyc_identity_is_nin(): void
    {
        $this->seedApiCenter();
        $user = $this->makeUser();

        VerificationResult::create([
            'user_id' => $user->id,
            'service_type' => 'bvn_verification',
            'identifier' => '22209291663',
            'provider_name' => 'Test',
            'response_data' => ['bvn' => '22209291663'],
            'status' => 'success',
            'reference_id' => 'BVN-OLDREF',
        ]);

        VerificationResult::create([
            'user_id' => $user->id,
            'service_type' => 'kyc_tier_nin',
            'identifier' => '12345678901',
            'provider_name' => 'Test',
            'response_data' => ['nin' => '12345678901'],
            'status' => 'success',
            'reference_id' => 'NIN-NEWREF',
        ]);

        Http::fake([
            'https://payvessel.test/reserve' => Http::response([
                'data' => ['accountNumber' => '9000000101', 'accountName' => 'Test User'],
            ], 200),
            'https://api.monnify.com/api/v1/auth/login' => Http::response([
                'responseBody' => ['accessToken' => 'token'],
            ], 200),
            'https://api.monnify.com/api/v2/bank-transfer/reserved-accounts' => Http::response([
                'responseBody' => [
                    'status' => 'ACTIVE',
                    'accountName' => 'Test User',
                    'accounts' => [
                        ['bankName' => 'Wema Bank', 'accountNumber' => '9000000102'],
                    ],
                ],
            ], 200),
            'https://paypoint.test/reserve' => Http::response([
                'status' => 'success',
                'bankAccounts' => [['accountNumber' => '9000000103']],
            ], 200),
            'https://api.paystack.co/customer' => Http::response([
                'status' => true,
                'data' => ['customer_code' => 'CUS_xxx'],
            ], 200),
            'https://api.paystack.co/dedicated_account' => Http::response([
                'status' => true,
                'data' => [
                    'account_number' => '9000000104',
                    'account_name' => 'TEST USER',
                    'bank' => ['name' => 'Wema Bank'],
                    'id' => 123,
                ],
            ], 200),
            'https://api.flutterwave.com/v3/virtual-account-numbers' => Http::response([
                'status' => 'success',
                'message' => 'Virtual account created',
                'data' => ['account_number' => '9000000105', 'bank_name' => 'Flutterwave Bank'],
            ], 200),
        ]);

        $res = app(VirtualAccountService::class)->ensureAccounts($user);
        $this->assertTrue($res['status']);

        Http::assertSent(function ($request) {
            if ($request->url() !== 'https://api.monnify.com/api/v2/bank-transfer/reserved-accounts') {
                return false;
            }

            $data = $request->data();
            return isset($data['nin']) && $data['nin'] === '12345678901' && ! isset($data['bvn']);
        });
    }

    public function test_it_does_not_create_more_when_limit_is_reached(): void
    {
        $this->seedApiCenter();
        $user = $this->makeUser();

        VirtualAccount::insert([
            ['user_id' => $user->id, 'gateway' => 'a', 'account_number' => '1', 'bank_name' => 'A', 'status' => 'active', 'currency' => 'NGN', 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => $user->id, 'gateway' => 'b', 'account_number' => '2', 'bank_name' => 'B', 'status' => 'active', 'currency' => 'NGN', 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => $user->id, 'gateway' => 'c', 'account_number' => '3', 'bank_name' => 'C', 'status' => 'active', 'currency' => 'NGN', 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => $user->id, 'gateway' => 'd', 'account_number' => '4', 'bank_name' => 'D', 'status' => 'active', 'currency' => 'NGN', 'created_at' => now(), 'updated_at' => now()],
        ]);

        Http::fake();

        $res = app(VirtualAccountService::class)->ensureAccounts($user);
        $this->assertTrue($res['status']);
        $this->assertSame(4, VirtualAccount::query()->where('user_id', $user->id)->whereIn('status', ['active', 'pending'])->count());

        Http::assertNothingSent();
    }
}

