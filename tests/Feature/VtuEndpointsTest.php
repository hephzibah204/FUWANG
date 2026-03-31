<?php

namespace Tests\Feature;

use App\Models\AccountBalance;
use App\Models\CustomApi;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VtuEndpointsTest extends TestCase
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

    public function test_airtime_to_cash_endpoint_credits_wallet(): void
    {
        $this->withoutMiddleware();

        $user = User::create(['email' => 'e2e-a2c@example.com', 'password' => 'secret']);
        AccountBalance::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'user_balance' => 0,
            'api_key' => 'user',
        ]);

        $provider = CustomApi::create([
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

        $res = $this->actingAs($user)->postJson('/services/vtu/airtime-to-cash/submit', [
            'provider_id' => $provider->id,
            'network' => 'MTN',
            'amount' => 1000,
            'phone' => '08123456789',
            'bank_code' => '058',
            'account_number' => '0123456789',
            'account_name' => 'Test User',
        ]);

        $res->assertStatus(200);
        $res->assertJson(['status' => true]);

        $bal = AccountBalance::where('user_id', $user->id)->firstOrFail();
        $this->assertSame(900.0, (float) $bal->user_balance);

        $this->assertNotNull(Transaction::where('user_email', $user->email)->first());
    }
}

