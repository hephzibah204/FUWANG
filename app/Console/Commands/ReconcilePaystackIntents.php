<?php

namespace App\Console\Commands;

use App\Models\ApiCenter;
use App\Models\PaymentIntent;
use App\Models\PaymentWebhookEvent;
use App\Models\User;
use App\Services\Payments\ManualCreditDetector;
use App\Services\WalletService;
use App\Support\PaymentProviderCredentials;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ReconcilePaystackIntents extends Command
{
    protected $signature = 'payments:reconcile-intents
        {--hours=1 : Only reconcile pending intents created more than this many hours ago}
        {--credit : Actually credit wallets for PAID intents that were not manually funded (default is a read-only dry-run)}
        {--limit=200 : Maximum number of pending intents to check in one run}';

    protected $description = 'Reconcile stale pending payment intents against the Paystack API; dry-run by default, --credit credits genuinely paid, not-manually-funded intents';

    public function handle(WalletService $walletService, ManualCreditDetector $manualCredits): int
    {
        $hours = max(0, (int) $this->option('hours'));
        $limit = max(1, (int) $this->option('limit'));
        $doCredit = (bool) $this->option('credit');

        $apiCenter = ApiCenter::first();
        $secretKey = PaymentProviderCredentials::paystack($apiCenter)['secret_key'];
        if (!$secretKey) {
            $this->error('Paystack secret key is not configured.');
            return self::FAILURE;
        }

        $intents = PaymentIntent::query()
            ->where('status', 'pending')
            ->where('created_at', '<=', now()->subHours($hours))
            ->orderBy('id')
            ->limit($limit)
            ->get();

        if ($intents->isEmpty()) {
            $this->info("No pending payment intents older than {$hours} hour(s).");
            return self::SUCCESS;
        }

        if (!$doCredit) {
            $this->warn('=== DRY-RUN MODE: read-only. No wallets will be credited and no records changed. Re-run with --credit to apply. ===');
        }

        $rows = [];
        $totals = ['PAID' => 0, 'NOT_PAID' => 0, 'CREDITED' => 0, 'WOULD_CREDIT' => 0, 'ALREADY_MANUALLY_FUNDED' => 0, 'ALREADY_PROCESSED' => 0, 'USER_NOT_FOUND' => 0];

        foreach ($intents as $intent) {
            $reference = (string) $intent->reference;
            $user = $intent->user_id ? User::find($intent->user_id) : null;
            $userLabel = $user ? $user->email : 'unknown';

            try {
                $res = Http::timeout(45)
                    ->withHeaders(['Authorization' => 'Bearer ' . $secretKey])
                    ->get('https://api.paystack.co/transaction/verify/' . urlencode($reference));
            } catch (\Throwable $e) {
                $rows[] = [$intent->id, $reference, $userLabel, $intent->amount_expected, 'NOT_PAID', 'API_ERROR: ' . $e->getMessage()];
                $totals['NOT_PAID']++;
                continue;
            }

            if (!$res->successful()) {
                $rows[] = [$intent->id, $reference, $userLabel, $intent->amount_expected, 'NOT_PAID', 'API_ERROR: HTTP ' . $res->status()];
                $totals['NOT_PAID']++;
                continue;
            }

            $data = $res->json('data');
            $gatewayStatus = Str::lower((string) ($data['status'] ?? 'unknown'));

            if (!$data || $gatewayStatus !== 'success') {
                $rows[] = [$intent->id, $reference, $userLabel, $intent->amount_expected, 'NOT_PAID', 'gateway status: ' . ($data ? $gatewayStatus : 'not found')];
                $totals['NOT_PAID']++;
                continue;
            }

            $totals['PAID']++;
            $amount = round(((int) ($data['amount'] ?? 0)) / 100, 2);
            if ($amount <= 0) {
                $amount = (float) $intent->amount_expected;
            }
            $gatewayRef = (string) ($data['reference'] ?? $reference);

            if (!$user) {
                $rows[] = [$intent->id, $reference, $userLabel, $amount, 'PAID', 'USER_NOT_FOUND'];
                $totals['USER_NOT_FOUND']++;
                continue;
            }

            // Already credited through the normal path (same reference)?
            if (DB::table('fundings')->where('reference', $reference)->orWhere('reference', $gatewayRef)->exists()) {
                $rows[] = [$intent->id, $reference, $user->email, $amount, 'PAID', 'ALREADY_PROCESSED'];
                $totals['ALREADY_PROCESSED']++;
                continue;
            }

            // Already funded manually by an admin (different reference)?
            $paidAt = null;
            if (!empty($data['paid_at'])) {
                try {
                    $paidAt = Carbon::parse((string) $data['paid_at']);
                } catch (\Throwable $e) {
                    $paidAt = null;
                }
            }
            $manualCredit = $manualCredits->findExistingManualCredit($user, $amount, $paidAt ?? $intent->created_at, $gatewayRef);
            if ($manualCredit) {
                $rows[] = [$intent->id, $reference, $user->email, $amount, 'PAID', 'ALREADY_MANUALLY_FUNDED (ref: ' . $manualCredit->reference . ')'];
                $totals['ALREADY_MANUALLY_FUNDED']++;
                continue;
            }

            if (!$doCredit) {
                $rows[] = [$intent->id, $reference, $user->email, $amount, 'PAID', 'WOULD_CREDIT'];
                $totals['WOULD_CREDIT']++;
                continue;
            }

            try {
                DB::transaction(function () use ($walletService, $user, $amount, $reference, $gatewayRef, $intent) {
                    $credit = $walletService->credit($user, $amount, 'Wallet Funding – Paystack', $reference);
                    if (!$credit['ok']) {
                        throw new \RuntimeException('Wallet credit was declined.');
                    }

                    try {
                        app(\App\Services\Referrals\ReferralService::class)->handleFunding($user, $amount, $reference, 'Wallet Funding – Paystack');
                    } catch (\Throwable $e) {
                        // Ignore referral error
                    }

                    $intent->gateway = 'paystack';
                    $intent->status = 'succeeded';
                    $intent->save();

                    DB::table('fundings')->insert([
                        'email' => $user->email,
                        'amount' => $amount,
                        'reference' => $reference,
                        'description' => 'Funding Wallet',
                        'funding_type' => 'Card Funding',
                        'fullname' => $user->fullname ?? $user->username ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    PaymentWebhookEvent::where('provider', 'paystack')
                        ->where(function ($q) use ($reference, $gatewayRef) {
                            $q->where('reference', $reference)
                              ->orWhere('reference', $gatewayRef)
                              ->orWhere('provider_event_id', $reference)
                              ->orWhere('provider_event_id', $gatewayRef);
                        })
                        ->update([
                            'processing_status' => 'succeeded',
                            'processed_at' => now(),
                            'processing_error' => null,
                        ]);
                });

                $rows[] = [$intent->id, $reference, $user->email, $amount, 'PAID', 'CREDITED'];
                $totals['CREDITED']++;
            } catch (\Throwable $e) {
                $rows[] = [$intent->id, $reference, $user->email, $amount, 'PAID', 'CREDIT_FAILED: ' . $e->getMessage()];
            }
        }

        $this->table(['Intent', 'Reference', 'User', 'Amount (NGN)', 'Paystack', 'Action'], $rows);
        $this->info(sprintf(
            'Totals: checked=%d, PAID=%d, NOT_PAID=%d, credited=%d, would_credit=%d, already_manually_funded=%d, already_processed=%d, user_not_found=%d',
            $intents->count(),
            $totals['PAID'],
            $totals['NOT_PAID'],
            $totals['CREDITED'],
            $totals['WOULD_CREDIT'],
            $totals['ALREADY_MANUALLY_FUNDED'],
            $totals['ALREADY_PROCESSED'],
            $totals['USER_NOT_FOUND']
        ));

        return self::SUCCESS;
    }
}
