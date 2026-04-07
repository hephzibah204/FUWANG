<?php

namespace App\Services\Referrals;

use App\Models\Referral;
use App\Models\ReferralAuditLog;
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
            $raw = random_bytes(6);
            $enc = strtoupper(rtrim(strtr(base64_encode($raw), '+/', 'AZ'), '='));
            $enc = preg_replace('/[^A-Z0-9]/', '', $enc) ?: '';

            $code = '';
            for ($j = 0; $j < strlen($enc) && strlen($code) < 8; $j++) {
                $ch = $enc[$j];
                if (str_contains($alphabet, $ch)) {
                    $code .= $ch;
                }
            }
            while (strlen($code) < 8) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }

            $exists = User::query()->where('referral_id', $code)->exists();
            if (!$exists) {
                return $code;
            }
        }

        return Str::upper(Str::random(8));
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

    public function recordRegistration(User $referrer, User $referred, string $code, ?array $context = null): Referral
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

        return $referral;
    }

    public function handleFunding(User $fundedUser, float $amount, string $transactionId, string $orderType): void
    {
        if (!str_starts_with($orderType, 'Wallet Funding')) {
            return;
        }

        $referral = Referral::query()
            ->where('referred_user_id', $fundedUser->id)
            ->lockForUpdate()
            ->first();

        if (!$referral || in_array($referral->status, ['funded', 'rewarded'], true)) {
            return;
        }

        DB::transaction(function () use ($referral, $fundedUser, $amount, $transactionId) {
            $ref = Referral::query()->where('id', $referral->id)->lockForUpdate()->first();
            if (!$ref || in_array($ref->status, ['funded', 'rewarded'], true)) {
                return;
            }

            $ref->status = 'funded';
            $ref->first_funded_at = now();
            $ref->save();

            $this->audit($ref, null, $ref->referrer_user_id, $ref->referred_user_id, 'referral_funded', 'succeeded', null, [
                'amount' => round($amount, 2),
                'tx' => $transactionId,
            ]);

            $referrer = User::query()->find($ref->referrer_user_id);
            if ($referrer) {
                $this->notifyReferrerFunded($referrer, $ref);
            }

            $rewardEnabled = (string) SystemSetting::get('referral_reward_enabled', 'false') === 'true';
            $rewardAmount = (float) SystemSetting::get('referral_reward_amount', 0);
            if (!$rewardEnabled || $rewardAmount <= 0) {
                return;
            }

            if (!$referrer) {
                return;
            }

            $rewardTx = 'REF-' . $ref->id . '-' . strtoupper(bin2hex(random_bytes(3)));
            $credit = app(WalletService::class)->credit($referrer, (float) $rewardAmount, 'Referral Reward', $rewardTx);
            if (!($credit['ok'] ?? false)) {
                $ref->reward_status = 'failed';
                $ref->save();
                $this->audit($ref, null, $ref->referrer_user_id, $ref->referred_user_id, 'referral_reward_failed', 'failed', 'Reward credit failed');
                return;
            }

            $ref->status = 'rewarded';
            $ref->reward_amount = round($rewardAmount, 2);
            $ref->reward_status = 'paid';
            $ref->reward_transaction_id = $rewardTx;
            $ref->save();

            $this->audit($ref, null, $ref->referrer_user_id, $ref->referred_user_id, 'referral_reward_paid', 'succeeded', null, [
                'amount' => round($rewardAmount, 2),
                'tx' => $rewardTx,
            ]);

            $this->notifyReferrerRewarded($referrer, $ref);
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

        return [
            'total' => $total,
            'registered' => $registered,
            'funded' => $funded,
            'rewarded' => $rewarded,
            'earnings' => round($earnings, 2),
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
            $referral = Referral::query()->where('referred_user_id', $currentUser->id)->first();
            if (!$referral) {
                break;
            }
            $referrer = User::query()->find($referral->referrer_user_id);
            if (!$referrer) {
                break;
            }
            $upline[$i] = $referrer;
            $currentUser = $referrer;
        }
        return $upline;
    }

    public function processTransaction(User $user, float $amount): void
    {
        $matrixEnabled = (string) SystemSetting::get('matrix_enabled', 'false') === 'true';
        if (!$matrixEnabled) {
            return;
        }

        $maxLevel = (int) SystemSetting::get('matrix_depth', 0);
        if ($maxLevel <= 0) {
            return;
        }

        $upline = $this->getUpline($user, $maxLevel);

        foreach ($upline as $level => $referrer) {
            $commissionRate = (float) SystemSetting::get('matrix_level_' . $level . '_percentage', 0);
            if ($commissionRate <= 0) {
                continue;
            }

            $commission = ($amount * $commissionRate) / 100;
            if ($commission <= 0) {
                continue;
            }

            $txId = 'COMM-' . $level . '-' . strtoupper(bin2hex(random_bytes(4)));
            app(WalletService::class)->credit($referrer, $commission, 'Matrix Commission (Level ' . $level . ')', $txId);

            $this->audit(null, null, $referrer->id, $user->id, 'matrix_commission_paid', 'succeeded', null, [
                'level' => $level,
                'amount' => round($commission, 2),
                'tx' => $txId,
                'original_tx_amount' => $amount,
            ]);
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
}
