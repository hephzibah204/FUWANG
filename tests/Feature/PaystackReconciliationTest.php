<?php

namespace Tests\Feature;

use App\Models\AccountBalance;
use App\Models\ApiCenter;
use App\Models\PaymentIntent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaystackReconciliationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.paystack.secret' => 'sk_test_reconcile']);

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
            'payvessel_secret_key' => 'pv_secret_test',
        ]);
    }

    private function makeIntent(string $reference, float $amount): PaymentIntent
    {
        $intent = PaymentIntent::create([
            'user_id' => $this->user->id,
            'reference' => $reference,
            'gateway' => null,
            'amount_expected' => $amount,
            'currency' => 'NGN',
            'status' => 'pending',
        ]);
        $intent->created_at = now()->subHours(2);
        $intent->save();

        return $intent;
    }

    #[Test]
    public function verify_paystack_refuses_to_double_credit_a_manually_funded_user(): void
    {
        $this->makeIntent('NXS-MANUAL1', 1000);

        // Admin already funded this user manually (different, UUID-style reference).
        DB::table('fundings')->insert([
            'email' => $this->user->email,
            'amount' => 1000,
            'reference' => '22403539-210b-44d0-b9b2-575b0ef43bb2',
            'description' => 'Admin Credit',
            'funding_type' => 'Admin Credit',
            'fullname' => 'Adebayo Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Http::fake([
            'api.paystack.co/*' => Http::response([
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'amount' => 100000,
                    'reference' => 'NXS-MANUAL1',
                    'customer' => ['email' => $this->user->email],
                    'paid_at' => now()->toIso8601String(),
                ],
            ], 200),
        ]);

        $exit = Artisan::call('payments:verify-paystack', ['reference' => 'NXS-MANUAL1']);

        $this->assertSame(0, $exit);
        $this->assertStringContainsString('already credited manually', Artisan::output());
        $this->assertEquals(0, (float) AccountBalance::where('user_id', $this->user->id)->value('user_balance'));
        $this->assertSame(0, DB::table('fundings')->where('reference', 'NXS-MANUAL1')->count());
        $this->assertSame('pending', PaymentIntent::where('reference', 'NXS-MANUAL1')->value('status'));
    }

    #[Test]
    public function reconcile_intents_dry_run_classifies_paid_and_abandoned_without_crediting(): void
    {
        $paid = $this->makeIntent('NXS-PAID1', 1000);
        $abandoned = $this->makeIntent('NXS-ABAN1', 500);

        Http::fake(function ($request) {
            if (str_contains($request->url(), 'NXS-PAID1')) {
                return Http::response([
                    'status' => true,
                    'data' => [
                        'status' => 'success',
                        'amount' => 100000,
                        'reference' => 'NXS-PAID1',
                        'customer' => ['email' => $this->user->email],
                        'paid_at' => now()->toIso8601String(),
                    ],
                ], 200);
            }

            return Http::response([
                'status' => true,
                'data' => [
                    'status' => 'abandoned',
                    'amount' => 50000,
                    'reference' => 'NXS-ABAN1',
                    'customer' => ['email' => $this->user->email],
                ],
            ], 200);
        });

        $exit = Artisan::call('payments:reconcile-intents');

        $this->assertSame(0, $exit);
        $output = Artisan::output();
        $this->assertStringContainsString('WOULD_CREDIT', $output);
        $this->assertStringContainsString('NOT_PAID', $output);

        // Dry-run must not credit or change intent state.
        $this->assertEquals(0, (float) AccountBalance::where('user_id', $this->user->id)->value('user_balance'));
        $this->assertSame('pending', $paid->refresh()->status);
        $this->assertSame('pending', $abandoned->refresh()->status);
        $this->assertSame(0, DB::table('fundings')->count());
    }
}
