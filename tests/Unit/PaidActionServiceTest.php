<?php

namespace Tests\Unit;

use App\Models\AccountBalance;
use App\Models\Transaction;
use App\Models\User;
use App\Services\PaidActionService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PaidActionServiceTest extends TestCase
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
    }

    public function test_run_marks_success_and_debits_wallet(): void
    {
        $user = User::create([
            'email' => 'paid@example.com',
            'password' => 'secret',
        ]);

        AccountBalance::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'user_balance' => 1000,
            'api_key' => 'user',
        ]);

        $paid = app(PaidActionService::class)->run($user, 200, 'Test Order', 'TST', function () {
            return ['ok' => true];
        });

        $this->assertTrue($paid['ok']);

        $balance = AccountBalance::where('user_id', $user->id)->firstOrFail();
        $this->assertSame(800.0, (float) $balance->user_balance);

        $tx = Transaction::where('transaction_id', $paid['txId'])->firstOrFail();
        $this->assertSame('success', $tx->status);
    }

    public function test_run_refunds_on_failure(): void
    {
        $user = User::create([
            'email' => 'refund@example.com',
            'password' => 'secret',
        ]);

        AccountBalance::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'user_balance' => 1000,
            'api_key' => 'user',
        ]);

        $paid = app(PaidActionService::class)->run($user, 200, 'Test Order', 'TST', function () {
            throw new \RuntimeException('Boom');
        });

        $this->assertFalse($paid['ok']);

        $balance = AccountBalance::where('user_id', $user->id)->firstOrFail();
        $this->assertSame(1000.0, (float) $balance->user_balance);

        $tx = Transaction::where('transaction_id', $paid['txId'])->firstOrFail();
        $this->assertSame('failed', $tx->status);

        $refund = Transaction::where('transaction_id', $paid['txId'] . '-RF')->first();
        $this->assertNotNull($refund);
        $this->assertSame('success', $refund->status);
    }
}

