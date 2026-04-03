<?php

namespace App\Services;

use App\Models\SystemSetting;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class KycService
{
    /**
     * Tier limits configuration
     * Returns limits for daily, monthly, and single transactions based on tier.
     */
    public function getTierLimits(int $tier): array
    {
        $limits = [
            0 => [
                'daily' => (float) SystemSetting::get('kyc_tier_0_daily_limit', 5000),
                'monthly' => (float) SystemSetting::get('kyc_tier_0_monthly_limit', 20000),
                'single' => (float) SystemSetting::get('kyc_tier_0_single_limit', 2000),
                'label' => 'Unverified',
            ],
            1 => [
                'daily' => (float) SystemSetting::get('kyc_tier_1_daily_limit', 50000),
                'monthly' => (float) SystemSetting::get('kyc_tier_1_monthly_limit', 200000),
                'single' => (float) SystemSetting::get('kyc_tier_1_single_limit', 20000),
                'label' => 'Tier 1 (Basic)',
            ],
            2 => [
                'daily' => (float) SystemSetting::get('kyc_tier_2_daily_limit', 500000),
                'monthly' => (float) SystemSetting::get('kyc_tier_2_monthly_limit', 2000000),
                'single' => (float) SystemSetting::get('kyc_tier_2_single_limit', 100000),
                'label' => 'Tier 2 (NIN/BVN)',
            ],
            3 => [
                'daily' => (float) SystemSetting::get('kyc_tier_3_daily_limit', 5000000),
                'monthly' => (float) SystemSetting::get('kyc_tier_3_monthly_limit', 20000000),
                'single' => (float) SystemSetting::get('kyc_tier_3_single_limit', 2000000),
                'label' => 'Tier 3 (VIP)',
            ],
        ];

        return $limits[$tier] ?? $limits[0];
    }

    /**
     * Check if a transaction is allowed for a user based on their KYC tier limits.
     */
    public function canTransact(User $user, float $amount): array
    {
        $tier = (int) ($user->kyc_tier ?? 0);
        $limits = $this->getTierLimits($tier);

        // 1. Single transaction limit check
        if ($amount > $limits['single']) {
            return [
                'allowed' => false,
                'message' => "Tier {$tier} limit: single transactions are capped at ₦" . number_format($limits['single'], 2) . ". Please upgrade your KYC to increase limits.",
            ];
        }

        // 2. Daily limit check
        $dailySpent = $this->getDailySpent($user);
        if (($dailySpent + $amount) > $limits['daily']) {
            $remaining = max(0, $limits['daily'] - $dailySpent);
            return [
                'allowed' => false,
                'message' => "Daily limit exceeded. Your current Tier allows ₦" . number_format($limits['daily'], 2) . " daily. You have already spent ₦" . number_format($dailySpent, 2) . ". Remaining today: ₦" . number_format($remaining, 2) . ".",
            ];
        }

        // 3. Monthly limit check
        $monthlySpent = $this->getMonthlySpent($user);
        if (($monthlySpent + $amount) > $limits['monthly']) {
            $remaining = max(0, $limits['monthly'] - $monthlySpent);
            return [
                'allowed' => false,
                'message' => "Monthly limit exceeded for Tier {$tier}. Total monthly allowed: ₦" . number_format($limits['monthly'], 2) . ". Remaining for this month: ₦" . number_format($remaining, 2) . ".",
            ];
        }

        return ['allowed' => true];
    }

    /**
     * Get total spent today by the user.
     */
    public function getDailySpent(User $user): float
    {
        return (float) Transaction::where('user_email', $user->email)
            ->whereIn('status', ['success', 'pending'])
            ->whereIn('order_type', $this->getDebitTypes())
            ->whereDate('created_at', Carbon::today())
            ->sum(DB::raw('ABS(balance_after - balance_before)'));
    }

    /**
     * Get total spent this month by the user.
     */
    public function getMonthlySpent(User $user): float
    {
        return (float) Transaction::where('user_email', $user->email)
            ->whereIn('status', ['success', 'pending'])
            ->whereIn('order_type', $this->getDebitTypes())
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum(DB::raw('ABS(balance_after - balance_before)'));
    }

    /**
     * List of transaction types that represent user debits/expenses.
     */
    public function getDebitTypes(): array
    {
        return [
            'Airtime Purchase',
            'Data Purchase',
            'Cable TV Payment',
            'Electricity Payment',
            'Educational E-PIN',
            'NIN Verification',
            'BVN Verification',
            'Identity Verification',
            'Service Payment',
            'Wallet Debit',
            'Admin Deduction',
            'Agency Banking Withdrawal'
        ];
    }
    
    /**
     * Refresh user KYC tier based on their existing verification records.
     */
    public function refreshUserTier(User $user): int
    {
        $currentTier = (int) ($user->kyc_tier ?? 0);
        $newTier = 0;
        
        // Tier 1: Email verified
        if ($user->email_verified_at) {
            $newTier = 1;
        }
        
        // Tier 2: NIN or BVN verified
        $hasNIN = \App\Models\VerificationResult::where('user_id', $user->id)
            ->where('service_type', 'nin')
            ->where('status', 'success')
            ->exists();
            
        $hasBVN = \App\Models\VerificationResult::where('user_id', $user->id)
            ->where('service_type', 'bvn')
            ->where('status', 'success')
            ->exists();
            
        if ($hasNIN || $hasBVN) {
            $newTier = 2;
        }
        
        // Tier 3: Advanced verification (Manual check usually)
        // We only upgrade to tier 3 if specifically set, but we can auto-upgrade to Tier 2
        
        if ($newTier > $currentTier) {
            $user->kyc_tier = $newTier;
            $user->save();
            return $newTier;
        }
        
        return $currentTier;
    }
}
