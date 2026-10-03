<?php

namespace Tests\Feature;

use App\Models\AccountBalance;
use App\Models\ApiCenter;
use App\Models\BankDetail;
use App\Models\PaymentWebhookEvent;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentWebhookFundingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $pvSecret = 'pv_secret_test_key_12345';
    private string $ppSecret = 'pp_secret_test_key_12345';
    private string $mfSecret = 'mf_secret_test_key_12345';

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'fullname' => 'Adebayo Test',
            'username' => 'adebayotest',
            'email' => 'adebayo@fuwa.ng',
            'password' => Hash::make('Secret123!'),
            'number' => '08031234567',
            'email_verified_at' => now(),
        ]);

        AccountBalance::create([
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'user_balance' => 0.00,
            'api_key' => 'user',
        ]);

        ApiCenter::create([
            'payvessel_api_key' => 'pv_api_key',
            'payvessel_secret_key' => $this->pvSecret,
            'paypoint_api_key' => 'pp_api_key',
            'paypoint_secret_key' => $this->ppSecret,
            'monnify_api_key' => 'mf_api_key',
            'monnify_secret_key' => $this->mfSecret,
            'monnify_contract_code' => '1234567890',
        ]);
    }

    #[Test]
    public function it_successfully_credits_wallet_on_payvessel_webhook()
    {
        BankDetail::create([
            'email' => $this->user->email,
            'psb9' => '9988776655',
            'account_name' => 'Fuwa / Adebayo Test',
        ]);

        $payload = [
            'event' => 'transfer.success',
            'transaction' => [
                'id' => 'PV_TX_1001',
                'reference' => 'PV_REF_1001',
                'amount' => 5000.00,
            ],
            'order' => [
                'settlement_amount' => 5000.00,
                'currency' => 'NGN',
                'bank_account' => '9988776655',
            ],
            'customer' => [
                'email' => 'adebayo@fuwa.ng',
                'name' => 'Adebayo Test',
            ],
        ];

        $json = json_encode($payload);
        $signature = hash_hmac('sha512', $json, $this->pvSecret);

        $response = $this->withHeaders([
            'payvessel-http-signature' => $signature,
            'Content-Type' => 'application/json',
        ])->postJson('/webhooks/payvessel', $payload);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Accepted']);

        // Check user balance: 5000 - 50 fee = 4950
        $balance = AccountBalance::where('email', $this->user->email)->first();
        $this->assertEquals(4950.00, (float) $balance->user_balance);

        // Check fundings table
        $this->assertDatabaseHas('fundings', [
            'email' => $this->user->email,
            'reference' => 'PV_REF_1001',
            'funding_type' => 'Automatic Funding',
        ]);

        // Check transactions table
        $this->assertDatabaseHas('transactions', [
            'user_email' => $this->user->email,
            'transaction_id' => 'PV_REF_1001',
            'status' => 'success',
        ]);

        // Check payment webhook events
        $this->assertDatabaseHas('payment_webhook_events', [
            'provider' => 'payvessel',
            'reference' => 'PV_REF_1001',
            'processing_status' => 'succeeded',
        ]);
    }

    #[Test]
    public function it_safely_credits_small_payvessel_deposit_without_crashing()
    {
        $payload = [
            'event' => 'transfer.success',
            'transaction' => [
                'id' => 'PV_TX_SMALL_1',
                'reference' => 'PV_REF_SMALL_1',
                'amount' => 30.00,
            ],
            'order' => [
                'settlement_amount' => 30.00,
                'currency' => 'NGN',
            ],
            'customer' => [
                'email' => 'adebayo@fuwa.ng',
            ],
        ];

        $json = json_encode($payload);
        $signature = hash_hmac('sha512', $json, $this->pvSecret);

        $response = $this->withHeaders([
            'PAYVESSEL_HTTP_SIGNATURE' => $signature,
            'Content-Type' => 'application/json',
        ])->postJson('/webhooks/payvessel', $payload);

        $response->assertStatus(200);

        // Should credit the full 30.00 since it is smaller than fee
        $balance = AccountBalance::where('email', $this->user->email)->first();
        $this->assertEquals(30.00, (float) $balance->user_balance);
    }

    #[Test]
    public function it_resolves_user_via_virtual_account_number_when_email_differs()
    {
        BankDetail::create([
            'email' => $this->user->email,
            'psb9' => '9988776699',
            'account_name' => 'Fuwa / Adebayo Test',
        ]);

        // The bank payload gives sender's personal email, not Fuwa email
        $payload = [
            'event' => 'transfer.success',
            'transaction' => [
                'id' => 'PV_TX_DIFF_EMAIL',
                'reference' => 'PV_REF_DIFF_EMAIL',
                'amount' => 1000.00,
            ],
            'order' => [
                'settlement_amount' => 1000.00,
                'currency' => 'NGN',
                'bank_account' => '9988776699',
            ],
            'customer' => [
                'email' => 'sender_foreign_bank@gmail.com',
            ],
        ];

        $json = json_encode($payload);
        $signature = hash_hmac('sha512', $json, $this->pvSecret);

        $response = $this->withHeaders([
            'payvessel-http-signature' => $signature,
            'Content-Type' => 'application/json',
        ])->postJson('/webhooks/payvessel', $payload);

        $response->assertStatus(200);

        // Should find user via psb9 virtual account number and credit 1000 - 50 = 950
        $balance = AccountBalance::where('email', $this->user->email)->first();
        $this->assertEquals(950.00, (float) $balance->user_balance);
    }

    #[Test]
    public function it_successfully_credits_wallet_on_palmpay_webhook()
    {
        BankDetail::create([
            'email' => $this->user->email,
            'palmpay' => '7788990011',
            'account_name' => 'Fuwa / Adebayo Test',
        ]);

        $payload = [
            'transaction_id' => 'PP_TX_5001',
            'settlement_amount' => 2000.00,
            'transaction_status' => 'success',
            'currency' => 'NGN',
            'customer' => [
                'email' => 'adebayo@fuwa.ng',
            ],
            'account_number' => '7788990011',
        ];

        $json = json_encode($payload);
        $signature = hash_hmac('sha256', $json, $this->ppSecret);

        $response = $this->withHeaders([
            'PAYMENTPOINT_SIGNATURE' => $signature,
            'Content-Type' => 'application/json',
        ])->postJson('/webhooks/palmpay', $payload);

        $response->assertStatus(200);

        // 2000 - 1% (20) = 1980
        $balance = AccountBalance::where('email', $this->user->email)->first();
        $this->assertEquals(1980.00, (float) $balance->user_balance);
    }

    #[Test]
    public function it_successfully_credits_wallet_on_monnify_webhook()
    {
        BankDetail::create([
            'email' => $this->user->email,
            'Wema_account' => '1234509876',
            'account_reference' => 'FUWA-MNFY-REF1',
            'account_name' => 'Fuwa / Adebayo Test',
        ]);

        $payload = [
            'eventType' => 'SUCCESSFUL_TRANSACTION',
            'eventData' => [
                'transactionReference' => 'MNFY_TX_8801',
                'paymentReference' => 'PAY_REF_8801',
                'amountPaid' => 3000.00,
                'settlementAmount' => 3000.00,
                'currency' => 'NGN',
                'customer' => [
                    'email' => 'adebayo@fuwa.ng',
                ],
                'destinationAccountInformation' => [
                    'accountNumber' => '1234509876',
                ],
            ],
        ];

        $json = json_encode($payload);
        $signature = hash_hmac('sha512', $json, $this->mfSecret);

        $response = $this->withHeaders([
            'monnify-signature' => $signature,
            'Content-Type' => 'application/json',
        ])->postJson('/webhooks/monnify', $payload);

        $response->assertStatus(200);

        $balance = AccountBalance::where('email', $this->user->email)->first();
        $this->assertEquals(3000.00, (float) $balance->user_balance);
    }

    #[Test]
    public function it_is_idempotent_and_prevents_double_crediting()
    {
        $payload = [
            'event' => 'transfer.success',
            'transaction' => [
                'id' => 'PV_TX_REPLAY_1',
                'reference' => 'PV_REF_REPLAY_1',
                'amount' => 1000.00,
            ],
            'order' => [
                'settlement_amount' => 1000.00,
                'currency' => 'NGN',
            ],
            'customer' => [
                'email' => 'adebayo@fuwa.ng',
            ],
        ];

        $json = json_encode($payload);
        $signature = hash_hmac('sha512', $json, $this->pvSecret);

        // First delivery
        $res1 = $this->withHeaders([
            'payvessel-http-signature' => $signature,
            'Content-Type' => 'application/json',
        ])->postJson('/webhooks/payvessel', $payload);

        $res1->assertStatus(200);
        $balance = AccountBalance::where('email', $this->user->email)->first();
        $this->assertEquals(950.00, (float) $balance->user_balance);

        // Second delivery (replay/retry from gateway)
        $res2 = $this->withHeaders([
            'payvessel-http-signature' => $signature,
            'Content-Type' => 'application/json',
        ])->postJson('/webhooks/payvessel', $payload);

        $res2->assertStatus(200);

        // Balance should still be 950.00, NOT 1900.00!
        $balanceAfter = AccountBalance::where('email', $this->user->email)->first();
        $this->assertEquals(950.00, (float) $balanceAfter->user_balance);
    }

    #[Test]
    public function it_rejects_invalid_signatures_with_403()
    {
        $payload = [
            'event' => 'transfer.success',
            'transaction' => [
                'reference' => 'PV_REF_FAKE',
            ],
            'order' => [
                'settlement_amount' => 1000.00,
            ],
            'customer' => [
                'email' => 'adebayo@fuwa.ng',
            ],
        ];

        $response = $this->withHeaders([
            'payvessel-http-signature' => 'invalid_fake_signature_hash',
            'Content-Type' => 'application/json',
        ])->postJson('/webhooks/payvessel', $payload);

        $response->assertStatus(403);

        $balance = AccountBalance::where('email', $this->user->email)->first();
        $this->assertEquals(0.00, (float) $balance->user_balance);
    }
}
