<?php

namespace Tests\Feature;

use App\Mail\WalletRefundMail;
use App\Models\AccountBalance;
use App\Models\Admin;
use App\Models\AdminAuditLog;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminUserRefundTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_refund_restores_balance_marks_refunded_audits_and_queues_mail(): void
    {
        Mail::fake();

        $admin = Admin::create([
            'username' => 'super_refund_test',
            'fullname' => 'Super',
            'email' => 'super-refund-test@example.com',
            'password' => Hash::make('password'),
            'is_super_admin' => true,
        ]);

        $user = User::create([
            'fullname' => 'Customer',
            'username' => 'customer1',
            'email' => 'customer-refund@example.com',
            'password' => Hash::make('secret'),
            'user_status' => 'active',
        ]);

        AccountBalance::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'user_balance' => 900,
            'api_key' => 'user',
        ]);

        $debitId = 'DEBIT-TEST-REFUND-1';
        Transaction::create([
            'user_email' => $user->email,
            'order_type' => 'Test purchase',
            'balance_before' => 1000,
            'balance_after' => 900,
            'transaction_id' => $debitId,
            'status' => 'success',
        ]);

        $this->actingAs($admin, 'admin')
            ->postJson(route('admin.users.refund'), [
                'transaction_id' => $debitId,
                'note' => 'Customer request',
            ])
            ->assertOk()
            ->assertJson(['status' => true]);

        $this->assertSame(1000.0, (float) AccountBalance::where('user_id', $user->id)->firstOrFail()->user_balance);

        $original = Transaction::where('transaction_id', $debitId)->firstOrFail();
        $this->assertSame('refunded', $original->status);

        $credit = Transaction::where('transaction_id', 'REF-' . $debitId)->firstOrFail();
        $this->assertSame('success', $credit->status);
        $this->assertSame(900.0, (float) $credit->balance_before);
        $this->assertSame(1000.0, (float) $credit->balance_after);

        $this->assertDatabaseHas('admin_audit_logs', [
            'admin_id' => $admin->id,
            'action' => 'admin.user.refund',
        ]);

        $log = AdminAuditLog::where('action', 'admin.user.refund')->firstOrFail();
        $this->assertSame($debitId, $log->meta['transaction_id'] ?? null);
        $this->assertSame(100.0, (float) ($log->meta['amount'] ?? 0));
        $this->assertSame('Customer request', $log->meta['note'] ?? null);

        Mail::assertQueued(WalletRefundMail::class);
    }

    public function test_refund_rejected_when_admin_refund_credit_already_exists(): void
    {
        $admin = Admin::create([
            'username' => 'super_refund_dup',
            'fullname' => 'Super',
            'email' => 'super-refund-dup@example.com',
            'password' => Hash::make('password'),
            'is_super_admin' => true,
        ]);

        $user = User::create([
            'fullname' => 'Customer',
            'username' => 'customer2',
            'email' => 'customer-dup@example.com',
            'password' => Hash::make('secret'),
            'user_status' => 'active',
        ]);

        AccountBalance::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'user_balance' => 1000,
            'api_key' => 'user',
        ]);

        $debitId = 'DEBIT-DUP-1';
        Transaction::create([
            'user_email' => $user->email,
            'order_type' => 'Test',
            'balance_before' => 1000,
            'balance_after' => 900,
            'transaction_id' => $debitId,
            'status' => 'success',
        ]);
        Transaction::create([
            'user_email' => $user->email,
            'order_type' => 'Admin Refund: ' . $debitId,
            'balance_before' => 900,
            'balance_after' => 1000,
            'transaction_id' => 'REF-' . $debitId,
            'status' => 'success',
        ]);

        $this->actingAs($admin, 'admin')
            ->postJson(route('admin.users.refund'), ['transaction_id' => $debitId])
            ->assertStatus(422);
    }
}
