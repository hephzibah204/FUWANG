<?php

namespace App\Console\Commands;

use App\Models\EnrollmentAgent;
use App\Services\AccountKycIdentityService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RetryPendingAgentNinVerification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agent:retry-pending-nin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automated background re-verification for enrollment agents with pending NIN server fallback';

    /**
     * Execute the console command.
     */
    public function handle(AccountKycIdentityService $kycService)
    {
        $pendingAgents = EnrollmentAgent::where('nin_server_status', 'pending_fallback')
            ->whereNotNull('nin')
            ->get();

        if ($pendingAgents->isEmpty()) {
            $this->info('No pending NIN agent verifications found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$pendingAgents->count()} agents pending NIN background verification.");
        $successCount = 0;

        foreach ($pendingAgents as $agent) {
            $user = $agent->user;
            if (!$user) {
                continue;
            }

            try {
                $verificationRes = $kycService->verifyNinForTier($user, $agent->nin);

                if (isset($verificationRes['status']) && (bool)$verificationRes['status'] === true) {
                    $verifiedName = trim(($verificationRes['data']['firstname'] ?? $verificationRes['firstname'] ?? '') . ' ' . ($verificationRes['data']['lastname'] ?? $verificationRes['lastname'] ?? ''));
                    $finalName = !empty($verifiedName) ? $verifiedName : $agent->full_name;

                    $agent->update([
                        'nin_verified' => true,
                        'nin_server_status' => 'verified',
                        'nin_verification_meta' => $verificationRes,
                        'full_name' => $finalName,
                    ]);

                    $user->update(['fullname' => $finalName]);
                    $successCount++;

                    Log::info("Automated NIN Verification Success for Agent #{$agent->id} (User #{$user->id})");
                }
            } catch (\Throwable $e) {
                Log::warning("Automated NIN Verification Retry failed for Agent #{$agent->id}: " . $e->getMessage());
            }
        }

        $this->info("Successfully verified {$successCount} pending agent NIN records.");
        return Command::SUCCESS;
    }
}
