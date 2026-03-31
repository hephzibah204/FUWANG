<?php

namespace Tests\Unit;

use App\Models\AccountBalance;
use App\Models\CustomApi;
use App\Models\Transaction;
use App\Models\User;
use App\Models\VtuTransaction;
use App\Services\VtuHubService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VtuHubServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('fullname')->nullable();
                $table->string('username')->nullable();
                $table->string('email')->unique();
                $table->string('password')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('account_balances')) {
            Schema::create('account_balances', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('email')->nullable();
                $table->decimal('user_balance', 18, 2)->default(0);
                $table->string('api_key')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->string('user_email')->nullable();
                $table->string('order_type')->nullable();
                $table->decimal('balance_before', 18, 2)->default(0);
                $table->decimal('balance_after', 18, 2)->default(0);
                $table->string('transaction_id')->nullable();
                $table->string('status')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('custom_apis')) {
            Schema::create('custom_apis', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('provider_identifier')->nullable();
                $table->string('service_type')->nullable();
                $table->string('endpoint')->nullable();
                $table->text('api_key')->nullable();
                $table->text('secret_key')->nullable();
                $table->text('headers')->nullable();
                $table->text('config')->nullable();
                $table->boolean('status')->default(true);
                $table->integer('priority')->default(1);
                $table->decimal('price', 18, 2)->nullable();
                $table->integer('timeout_seconds')->nullable();
                $table->integer('retry_count')->nullable();
                $table->integer('retry_delay_ms')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('vtu_transactions')) {
            Schema::create('vtu_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('custom_api_id')->nullable();
                $table->string('service_type', 80);
                $table->string('direction', 20)->default('debit');
                $table->decimal('amount', 18, 2)->default(0);
                $table->decimal('fee', 18, 2)->default(0);
                $table->decimal('total', 18, 2)->default(0);
                $table->string('transaction_id', 64)->unique();
                $table->string('status', 30)->default('pending');
                $table->json('request_payload')->nullable();
                $table->json('response_payload')->nullable();
                $table->string('provider_reference', 120)->nullable();
                $table->string('error_message', 255)->nullable();
                $table->timestamps();
            });
        }
    }

    public function test_debit_service_applies_fee_and_creates_vtu_transaction(): void
    {
        $user = User::create(['email' => 'vtu@example.com', 'password' => 'secret']);
        Auth::login($user);

        AccountBalance::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'user_balance' => 1000,
            'api_key' => 'user',
        ]);

        CustomApi::create([
            'name' => 'VTU Provider',
            'service_type' => 'vtu_internet',
            'endpoint' => 'https://example.com/vtu/internet',
            'api_key' => 'k',
            'headers' => json_encode([]),
            'config' => json_encode(['fee_type' => 'percent', 'fee_value' => 10]),
            'status' => true,
            'priority' => 1,
            'price' => 0,
        ]);

        Http::fake([
            'https://example.com/vtu/internet' => Http::response(['status' => 'success', 'data' => ['ok' => true]], 200),
        ]);

        $res = app(VtuHubService::class)->processRequest([
            'service_type' => 'vtu_internet',
            'request_id' => 'NET-TEST1234',
            'payload' => [
                'serviceID' => 'isp',
                'variation_code' => 'plan',
                'customer_id' => 'cust',
                'amount' => 200,
                'phone' => '08123456789',
            ],
        ]);

        $this->assertTrue($res['status']);

        $balance = AccountBalance::where('user_id', $user->id)->firstOrFail();
        $this->assertSame(780.0, (float) $balance->user_balance);

        $tx = Transaction::where('transaction_id', 'NET-TEST1234')->firstOrFail();
        $this->assertSame('success', $tx->status);

        $vtu = VtuTransaction::where('transaction_id', 'NET-TEST1234')->firstOrFail();
        $this->assertSame('vtu_internet', $vtu->service_type);
        $this->assertSame('debit', $vtu->direction);
        $this->assertSame(200.0, (float) $vtu->amount);
        $this->assertSame(20.0, (float) $vtu->fee);
        $this->assertSame(220.0, (float) $vtu->total);
        $this->assertSame('success', $vtu->status);
    }

    public function test_credit_service_credits_wallet_after_success(): void
    {
        $user = User::create(['email' => 'a2c@example.com', 'password' => 'secret']);
        Auth::login($user);

        AccountBalance::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'user_balance' => 100,
            'api_key' => 'user',
        ]);

        CustomApi::create([
            'name' => 'A2C Provider',
            'service_type' => 'vtu_airtime_to_cash',
            'endpoint' => 'https://example.com/vtu/a2c',
            'api_key' => 'k',
            'headers' => json_encode([]),
            'config' => json_encode(['fee_type' => 'flat', 'fee_value' => 100, 'credit_amount_path' => 'data.amount']),
            'status' => true,
            'priority' => 1,
            'price' => 0,
        ]);

        Http::fake([
            'https://example.com/vtu/a2c' => Http::response(['status' => 'success', 'data' => ['amount' => 900]], 200),
        ]);

        $res = app(VtuHubService::class)->processRequest([
            'service_type' => 'vtu_airtime_to_cash',
            'request_id' => 'A2C-TEST1234',
            'payload' => [
                'network' => 'MTN',
                'amount' => 1000,
                'phone' => '08123456789',
                'bank_code' => '058',
                'account_number' => '0123456789',
                'account_name' => 'Test User',
            ],
        ]);

        $this->assertTrue($res['status']);

        $balance = AccountBalance::where('user_id', $user->id)->firstOrFail();
        $this->assertSame(1000.0, (float) $balance->user_balance);

        $tx = Transaction::where('transaction_id', 'A2C-TEST1234')->firstOrFail();
        $this->assertSame('success', $tx->status);

        $vtu = VtuTransaction::where('transaction_id', 'A2C-TEST1234')->firstOrFail();
        $this->assertSame('credit', $vtu->direction);
        $this->assertSame('success', $vtu->status);
    }

    public function test_invalid_payload_returns_validation_error(): void
    {
        $user = User::create(['email' => 'bad@example.com', 'password' => 'secret']);
        Auth::login($user);

        AccountBalance::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'user_balance' => 1000,
            'api_key' => 'user',
        ]);

        $res = app(VtuHubService::class)->processRequest([
            'service_type' => 'vtu_airtime',
            'payload' => [
                'network' => 'MTN',
                'amount' => 100,
                'phone' => '123',
            ],
        ]);

        $this->assertFalse($res['status']);
        $this->assertArrayHasKey('errors', $res);
    }

    public function test_validate_customer_returns_customer_fields(): void
    {
        $user = User::create(['email' => 'val@example.com', 'password' => 'secret']);
        Auth::login($user);

        CustomApi::create([
            'name' => 'Elec Provider',
            'service_type' => 'vtu_electricity',
            'endpoint' => 'https://example.com/vtu/electricity/pay',
            'api_key' => 'k',
            'headers' => json_encode([]),
            'config' => json_encode([
                'validate_endpoint' => 'https://example.com/vtu/electricity/validate',
                'validate_customer_name_path' => 'data.customer.name',
                'validate_customer_address_path' => 'data.customer.address',
            ]),
            'status' => true,
            'priority' => 1,
            'price' => 0,
        ]);

        Http::fake([
            'https://example.com/vtu/electricity/validate' => Http::response([
                'status' => 'success',
                'data' => [
                    'customer' => [
                        'name' => 'Jane Doe',
                        'address' => 'IBEDC Area',
                    ],
                ],
            ], 200),
        ]);

        $res = app(VtuHubService::class)->validateCustomer('vtu_electricity', [
            'serviceID' => 'ibadan-electric',
            'variation_code' => 'prepaid',
            'meter_number' => '1234567890',
        ]);

        $this->assertTrue($res['status']);
        $this->assertSame('Jane Doe', $res['customer']['name']);
        $this->assertSame('IBEDC Area', $res['customer']['address']);
    }
}
