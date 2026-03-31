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

class VtuElectricityValidationTest extends TestCase
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

    public function test_validate_then_pay_electricity(): void
    {
        $this->withoutMiddleware();

        $user = User::create(['email' => 'elec@example.com', 'password' => 'secret']);
        AccountBalance::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'user_balance' => 10000,
            'api_key' => 'user',
        ]);

        $provider = CustomApi::create([
            'name' => 'Elec Provider',
            'service_type' => 'vtu_electricity',
            'endpoint' => 'https://example.com/vtu/electricity/pay',
            'api_key' => 'k',
            'headers' => json_encode([]),
            'config' => json_encode([
                'fee_type' => 'flat',
                'fee_value' => 50,
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
                'data' => ['customer' => ['name' => 'Jane Doe', 'address' => 'EKEDC Zone']],
            ], 200),
            'https://example.com/vtu/electricity/pay' => Http::response([
                'status' => 'success',
                'data' => ['token' => '1234-5678-9012'],
            ], 200),
        ]);

        $validate = $this->actingAs($user)->postJson('/services/vtu/electricity/validate', [
            'provider_id' => $provider->id,
            'serviceID' => 'eko-electric',
            'variation_code' => 'prepaid',
            'meter_number' => '1234567890',
        ]);
        $validate->assertStatus(200);
        $token = $validate->json('validation_token');
        $this->assertNotEmpty($token);

        $buyWithout = $this->actingAs($user)->postJson('/services/vtu/electricity/buy', [
            'provider_id' => $provider->id,
            'serviceID' => 'eko-electric',
            'variation_code' => 'prepaid',
            'meter_number' => '1234567890',
            'amount' => 500,
            'phone' => '08123456789',
            'validation_token' => 'wrong',
        ]);
        $buyWithout->assertStatus(422);

        $buy = $this->actingAs($user)->postJson('/services/vtu/electricity/buy', [
            'provider_id' => $provider->id,
            'serviceID' => 'eko-electric',
            'variation_code' => 'prepaid',
            'meter_number' => '1234567890',
            'amount' => 500,
            'phone' => '08123456789',
            'validation_token' => $token,
        ]);
        $buy->assertStatus(200);
        $buy->assertJson(['status' => true]);

        $tx = Transaction::where('user_email', $user->email)->latest('id')->firstOrFail();
        $this->assertSame('success', $tx->status);
    }
}

