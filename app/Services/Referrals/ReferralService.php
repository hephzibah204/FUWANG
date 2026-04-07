<?php

namespace App\Services\Referrals;

use App\Models\Referral;
use App\Models\ReferralAuditLog;
use App\Models\ReferralTier;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReferralService
{
    public function generateReferralCode(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        for ($i = 0; $i < 20; $i++) {
            $code = '';
            for ($j = 0; $j < 8; $j++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }

            if (!User::query()->where('referral_id', $code)->exists()) {
                return $code;
            }
        }

        // Fallback to a longer, more random string if a unique 8-character code can't be found in 20 attempts
        return Str::upper(Str::random(12));
    }

    public function normalizeCode(?string $code): ?string
    {
        if ($code === null) {
            return null;
        }
        $value = strtoupper(trim($code));
        if ($value === '' || $value === 'DEFAULT') {
            return null;
        }
        return $value;
    }

    public function findReferrerByCode(string $code): ?User
    {
        return User::query()->where('referral_id', $code)->first();
    }

    public function recordRegistration(User $referrer, User $referred, string $code, ?array $context = null): void
    {
        $referral = Referral::create([
            'referrer_user_id' => $referrer->id,
            'referred_user_id' => $referred->id,
            'referral_code' => $code,
            'status' => 'registered',
            'registered_at' => now(),
            'reward_amount' => 0,
            'reward_status' => 'none',
            'meta' => $context,
        ]);

        $this->audit($referral, null, $referrer->id, $referred->id, 'referral_registered', 'succeeded', null, [
            'referred_email_hash' => hash('sha256', strtolower((string) $referred->email)),
        ]);

        $this->notifyReferrerRegistered($referrer, $referral);
    }

    public function handleFunding(User $fundedUser, float $amount, string $transactionId, string $orderType): void
    {
        if (!Str::startsWith($orderType, 'Wallet Funding')) {
            return;
        }

        $referral = Referral::query()
            ->where('referred_user_id', $fundedUser->id)
            ->where('status', 'registered')
            ->first();

        if (!$referral) {
            return;
        }

        DB::transaction(function () use ($referral, $amount, $transactionId) {
            $referral->update([
                'status' => 'funded',
                'first_funded_at' => now(),
            ]);

            $this->audit($referral, null, $referral->referrer_user_id, $referral->referred_user_id, 'referral_funded', 'succeeded', null, [
                'amount' => round($amount, 2),
                'tx' => $transactionId,
            ]);

            if ($referrer = $referral->referrer) {
                $this->notifyReferrerFunded($referrer, $referral);
                $this->rewardReferrer($referrer, $referral, $amount);
            }
        });
    }

    public function statsForUser(User $user): array
    {
        $base = Referral::query()->where('referrer_user_id', $user->id);

        $total = (clone $base)->count();
        $registered = (clone $base)->whereIn('status', ['registered', 'funded', 'rewarded'])->count();
        $funded = (clone $base)->whereIn('status', ['funded', 'rewarded'])->count();
        $rewarded = (clone $base)->where('status', 'rewarded')->count();
        $earnings = (float) (clone $base)->where('reward_status', 'paid')->sum('reward_amount');

        $tier = ReferralTier::where('minimum_referrals', '<=', $funded)->orderBy('minimum_referrals', 'desc')->first();

        return [
            'total' => $total,
            'registered' => $registered,
            'funded' => $funded,
            'rewarded' => $rewarded,
            'earnings' => round($earnings, 2),
            'tier' => $tier->name ?? 'Bronze',
        ];
    }

    public function recentForUser(User $user, int $limit = 20)
    {
        return Referral::query()
            ->with(['referred:id,fullname,username,email,created_at'])
            ->where('referrer_user_id', $user->id)
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public function referralLink(User $user): string
    {
        return url('/register?ref=' . urlencode((string) $user->referral_id));
    }

    public function ensureUserReferralCode(User $user): void
    {
        if (!empty($user->referral_id)) {
            return;
        }
        $user->referral_id = $this->generateReferralCode();
        $user->save();
    }
    
    public function getUpline(User $user, int $maxLevel): array
    {
        $upline = [];
        $currentUser = $user;

        for ($i = 1; $i <= $maxLevel; $i++) {
            $referral = Referral::query()
                ->where('referred_user_id', $currentUser->id)
                ->with('referrer') // Eager load the referrer
                ->first();

            if (!$referral || !$referral->referrer) {
                break;
            }

            $upline[$i] = $referral->referrer;
            $currentUser = $referral->referrer;
        }

        return $upline;
    }

    public function processTransaction(User $user, float $amount): void
    {
        if ((string) SystemSetting::get('matrix_enabled', 'false') !== 'true') {
            return;
        }

        $maxLevel = (int) SystemSetting::get('matrix_depth', 0);
        if ($maxLevel <= 0) {
            return;
        }

        $upline = $this->getUpline($user, $maxLevel);

        foreach ($upline as $level => $referrer) {
            $this->payoutCommission($user, $referrer, $level, $amount);
        }
    }


    private function audit(?Referral $referral, ?int $actorUserId, int $referrerUserId, ?int $referredUserId, string $action, string $status, ?string $message = null, ?array $context = null): void
    {
        ReferralAuditLog::create([
            'referral_id' => $referral?->id,
            'user_id' => $actorUserId ?? $referrerUserId,
            'action' => $action,
            'description' => $message ?? $status,
            'metadata' => $context,
            'created_at' => now(),
        ]);

        $level = $status === 'failed' ? 'warning' : 'info';
        Log::{$level}('Referral audit', [
            'referral_id' => $referral?->id,
            'referrer_user_id' => $referrerUserId,
            'referred_user_id' => $referredUserId,
            'action' => $action,
            'status' => $status,
            'message' => $message,
        ]);
    }

    private function notifyReferrerFunded(User $referrer, Referral $referral): void
    {
        try {
            $referrer->notify(new \App\Notifications\ReferralFundedNotification($referral));
        } catch (\Throwable $e) {
            Log::warning('Referral funded notification failed', ['error' => $e->getMessage()]);
        }
    }

    public function notifyReferrerRegistered(User $referrer, Referral $referral): void
    {
        try {
            $referrer->notify(new \App\Notifications\ReferralRegisteredNotification($referral));
        } catch (\Throwable $e) {
            Log::warning('Referral registered notification failed', ['error' => $e->getMessage()]);
        }
    }

    private function notifyReferrerRewarded(User $referrer, Referral $referral): void
    {
        try {
            $referrer->notify(new \App\Notifications\ReferralRewardIssuedNotification($referral));
        } catch (\Throwable $e) {
            Log::warning('Referral reward notification failed', ['error' => $e->getMessage()]);
        }
    }

    private function rewardReferrer(User $referrer, Referral $referral, float $fundedAmount): void
    {
        $rewardEnabled = (string) SystemSetting::get('referral_reward_enabled', 'false') === 'true';

        if (!$rewardEnabled) {
            return;
        }

        $referralCount = Referral::query()->where('referrer_user_id', $referrer->id)->whereIn('status', ['funded', 'rewarded'])->count();
        $tier = ReferralTier::where('minimum_referrals', '<', $referralCount)->orderBy('minimum_referrals', 'desc')->first();

        if (!$tier) {
            return;
        }

        $commission = ($fundedAmount * $tier->commission_rate) / 100;

        if ($commission <= 0) {
            return;
        }

        $rewardTx = 'REF-' . $referral->id . '-' . strtoupper(bin2hex(random_bytes(3)));
        $credit = app(WalletService::class)->credit($referrer, (float) $commission, 'Referral Reward', $rewardTx);

        if ($credit['ok'] ?? false) {
            $referral->update([
                'status' => 'rewarded',
                'reward_amount' => round($commission, 2),
                'reward_status' => 'paid',
                'reward_transaction_id' => $rewardTx,
            ]);

            $this->audit($referral, null, $referral->referrer_user_id, $referral->referred_user_id, 'referral_reward_paid', 'succeeded', null, [
                'amount' => round($commission, 2),
                'tx' => $rewardTx,
            ]);

            $this->notifyReferrerRewarded($referrer, $referral);
        } else {
            $referral->update(['reward_status' => 'failed']);
            $this->audit($referral, null, $referral->referrer_user_id, $referral->referred_user_id, 'referral_reward_failed', 'failed', 'Reward credit failed');
        }
    }

    private function payoutCommission(User $originalUser, User $referrer, int $level, float $originalAmount): void
    {
        $commissionRate = (float) SystemSetting::get('matrix_level_' . $level . '_percentage', 0);
        if ($commissionRate <= 0) {
            return;
        }

        $commission = ($originalAmount * $commissionRate) / 100;
        if ($commission <= 0) {
            return;
        }

        $txId = 'COMM-' . $level . '-' . strtoupper(bin2hex(random_bytes(4)));
        app(WalletService::class)->credit($referrer, $commission, 'Matrix Commission (Level ' . $level . ')', $txId);

        $this->audit(null, null, $referrer->id, $originalUser->id, 'matrix_commission_paid', 'succeeded', null, [
            'level' => $level,
            'amount' => round($commission, 2),
            'tx' => $txId,
            'original_tx_amount' => $originalAmount,
        ]);
    }
}
