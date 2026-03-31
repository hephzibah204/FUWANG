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

class ApiVerificationWorkflowTest extends TestCase
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

        if (!Schema::hasTable('verification_results')) {
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
    }

    public function test_api_nin_verification_debits_and_persists_on_success(): void
    {
        $this->withoutMiddleware();

        $user = User::create([
            'email' => 'api-success@example.com',
            'password' => 'secret',
        ]);

        AccountBalance::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'user_balance' => 1000,
            'api_key' => 'user',
        ]);

        CustomApi::create([
            'name' => 'ProviderX',
            'service_type' => 'nin_verification',
            'endpoint' => 'https://example.com/nin',
            'status' => true,
            'priority' => 1,
            'price' => 200,
        ]);

        Http::fake([
            'https://example.com/nin/*' => Http::response([
                'status' => 'success',
                'data' => ['nin' => '123', 'photo' => 'x'],
            ], 200),
        ]);

        $res = $this->actingAs($user)->postJson('/api/v1/verifications/nin', [
            'number' => '123',
            'firstname' => 'A',
            'lastname' => 'B',
            'dob' => '2000-01-01',
            'mode' => 'nin',
        ]);

        $res->assertStatus(200);
        $res->assertJson(['status' => true]);

        $balance = AccountBalance::where('user_id', $user->id)->firstOrFail();
        $this->assertSame(800.0, (float) $balance->user_balance);

        $tx = Transaction::where('user_email', $user->email)->latest('id')->firstOrFail();
        $this->assertSame('success', $tx->status);

        $this->assertDatabaseHas('verification_results', [
            'user_id' => $user->id,
            'service_type' => 'nin_verification',
            'provider_name' => 'ProviderX',
            'status' => 'success',
        ]);
    }

    public function test_api_nin_verification_refunds_on_provider_error(): void
    {
        $this->withoutMiddleware();

        $user = User::create([
            'email' => 'api-fail@example.com',
            'password' => 'secret',
        ]);

        AccountBalance::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'user_balance' => 1000,
            'api_key' => 'user',
        ]);

        CustomApi::create([
            'name' => 'ProviderX',
            'service_type' => 'nin_verification',
            'endpoint' => 'https://example.com/nin',
            'status' => true,
            'priority' => 1,
            'price' => 200,
        ]);

        Http::fake([
            'https://example.com/nin/*' => Http::response([
                'status' => 'error',
                'message' => 'Provider down',
            ], 500),
        ]);

        $res = $this->actingAs($user)->postJson('/api/v1/verifications/nin', [
            'number' => '123',
            'firstname' => 'A',
            'lastname' => 'B',
            'dob' => '2000-01-01',
            'mode' => 'nin',
        ]);

        $res->assertStatus(502);
        $res->assertJson(['status' => false]);

        $balance = AccountBalance::where('user_id', $user->id)->firstOrFail();
        $this->assertSame(1000.0, (float) $balance->user_balance);

        $tx = Transaction::where('user_email', $user->email)->latest('id')->firstOrFail();
        $this->assertSame('success', $tx->status);

        $failed = Transaction::where('user_email', $user->email)
            ->where('status', 'failed')
            ->first();
        $this->assertNotNull($failed);
    }
}

