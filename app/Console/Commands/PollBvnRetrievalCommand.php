<?php

namespace App\Console\Commands;

use App\Models\BvnRetrievalRequest;
use App\Models\CustomApi;
use App\Services\DataVerify\DataVerifyClient;
use App\Services\VerificationResultService;
use App\Services\WalletService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PollBvnRetrievalCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bvn:poll-retrievals {--limit=50 : Maximum number of requests to check per run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll status of pending BVN retrieval requests from DataVerify and resolve outcomes';

    /**
     * Execute the console command.
     */
    public function handle(WalletService $wallet, VerificationResultService $vaultService): int
    {
        $limit = (int) $this->option('limit');
        $pending = BvnRetrievalRequest::query()
            ->where('status', 'pending')
            ->orderBy('id', 'asc')
            ->limit($limit)
            ->get();

        if ($pending->isEmpty()) {
            $this->info('No pending BVN retrieval requests to process.');
            return Command::SUCCESS;
        }

        $this->info("Found {$pending->count()} pending BVN retrieval requests. Polling DataVerify...");

        $provider = CustomApi::where('service_type', 'bvn_retrieval')->where('status', true)->first()
            ?? CustomApi::where('provider_identifier', 'like', '%dataverify%')->where('status', true)->first();

        $client = new DataVerifyClient($provider);
        $completedCount = 0;
        $refundedCount = 0;
        $pendingCount = 0;

        foreach ($pending as $request) {
            $txToCheck = $request->provider_transaction_id ?: $request->transaction_id;
            if (!$txToCheck) {
                continue;
            }

            try {
                $statusRes = $client->checkBvnRetrievalStatus($txToCheck);

                if ($statusRes['status'] === 'completed' && !empty($statusRes['bvn'])) {
                    $bvn = (string) $statusRes['bvn'];
                    $request->update([
                        'status' => 'completed',
                        'retrieved_bvn' => $bvn,
                        'completed_at' => now(),
                        'provider_response' => $statusRes['data'] ?? [],
                    ]);

                    if ($request->user) {
                        try {
                            $vaultService->create(
                                $request->user,
                                'bvn_retrieval',
                                $bvn,
                                'DataVerify BVN Retrieval',
                                array_merge((array) ($statusRes['data'] ?? []), [
                                    'phone_number' => $request->phone_number,
                                    'full_name' => $request->full_name,
                                    'dob' => $request->dob,
                                    'bvn' => $bvn,
                                ]),
                                'success',
                                'BVNRET'
                            );
                        } catch (\Throwable $ve) {
                            Log::warning('PollBvnRetrieval: failed creating vault record: ' . $ve->getMessage());
                        }
                    }

                    $completedCount++;
                    $this->info("Request #{$request->id} ({$request->phone_number}) completed with BVN: {$bvn}");
                    Log::info("PollBvnRetrieval: request #{$request->id} completed successfully.");
                } elseif ($statusRes['status'] === 'not_found' || !empty($statusRes['refund'])) {
                    // Refund to user wallet
                    if ($request->user && (float) $request->amount > 0) {
                        $refundTxId = 'RF-' . $request->transaction_id;
                        try {
                            $wallet->credit(
                                $request->user,
                                (float) $request->amount,
                                'Refund – BVN Retrieval Not Found (' . $request->phone_number . ')',
                                $refundTxId
                            );
                        } catch (\Throwable $we) {
                            Log::error('PollBvnRetrieval: refund failed for request #' . $request->id . ': ' . $we->getMessage());
                        }
                    }

                    $request->update([
                        'status' => 'refunded',
                        'failure_reason' => $statusRes['message'] ?? 'BVN record not found for this phone and name.',
                        'refunded_at' => now(),
                        'provider_response' => $statusRes['data'] ?? [],
                    ]);

                    $refundedCount++;
                    $this->warn("Request #{$request->id} ({$request->phone_number}) not found. Auto-refunded ₦{$request->amount}");
                    Log::info("PollBvnRetrieval: request #{$request->id} refunded (not found).");
                } else {
                    $pendingCount++;
                }
            } catch (\Throwable $e) {
                Log::warning("PollBvnRetrieval: error checking request #{$request->id}: " . $e->getMessage());
            }
        }

        $this->info("Poll completed: {$completedCount} completed, {$refundedCount} refunded, {$pendingCount} still pending.");
        return Command::SUCCESS;
    }
}
