<?php

namespace App\Services;

use App\Models\EnrollmentAgent;
use App\Models\LicenseDiscountCode;
use App\Models\LicenseDiscountRedemption;
use App\Models\User;

/**
 * Percentage discount codes for the agent NIN license fee.
 *
 * Rules (owner request 2026-11-27): codes are created by admins, applied
 * by agents at checkout, and each code can be redeemed at most once per
 * user (enforced by the unique (code, user) pair plus the checks here).
 * A redemption starts as a "pending" reservation when a checkout begins
 * and becomes "completed" only when the payment actually lands, so an
 * abandoned checkout never burns the agent's one use: re-starting the
 * checkout simply refreshes their own pending reservation.
 */
class LicenseDiscountService
{
    /**
     * Resolve a code for a user.
     *
     * @return array{ok: bool, message: string, discount: ?LicenseDiscountCode}
     */
    public function findUsable(string $rawCode, User $user): array
    {
        $code = strtoupper(trim($rawCode));
        if ($code === '') {
            return ['ok' => false, 'message' => 'Enter a discount code.', 'discount' => null];
        }

        $discount = LicenseDiscountCode::where('code', $code)->first();
        if (!$discount) {
            return ['ok' => false, 'message' => 'Discount code not recognized.', 'discount' => null];
        }

        if (!$discount->is_active) {
            return ['ok' => false, 'message' => 'This discount code is no longer active.', 'discount' => null];
        }

        if ($discount->isExpired()) {
            return ['ok' => false, 'message' => 'This discount code has expired.', 'discount' => null];
        }

        $mine = LicenseDiscountRedemption::where('license_discount_code_id', $discount->id)
            ->where('user_id', $user->id)
            ->first();

        if ($mine && $mine->status === 'completed') {
            return ['ok' => false, 'message' => 'You have already used this discount code.', 'discount' => null];
        }

        if ($discount->max_uses !== null) {
            $others = LicenseDiscountRedemption::where('license_discount_code_id', $discount->id)
                ->when($mine, fn ($q) => $q->where('id', '!=', $mine->id))
                ->count();
            if ($others >= $discount->max_uses) {
                return ['ok' => false, 'message' => 'This discount code has reached its usage limit.', 'discount' => null];
            }
        }

        return ['ok' => true, 'message' => 'Discount applied.', 'discount' => $discount];
    }

    /**
     * @return array{discount_amount: float, payable: float}
     */
    public function quote(LicenseDiscountCode $discount, float $fee): array
    {
        $discountAmount = round($fee * $discount->percent / 100, 2);

        return [
            'discount_amount' => $discountAmount,
            'payable' => round($fee - $discountAmount, 2),
        ];
    }

    /** Create or refresh the user's reservation for a checkout reference. */
    public function reserve(LicenseDiscountCode $discount, User $user, EnrollmentAgent $agent, string $reference, float $amountSaved, string $status = 'pending'): LicenseDiscountRedemption
    {
        return LicenseDiscountRedemption::updateOrCreate(
            ['license_discount_code_id' => $discount->id, 'user_id' => $user->id],
            [
                'agent_id' => $agent->id,
                'reference' => $reference,
                'amount_saved' => $amountSaved,
                'status' => $status,
            ]
        );
    }

    public function completeByReference(string $reference): void
    {
        LicenseDiscountRedemption::where('reference', $reference)
            ->where('status', 'pending')
            ->update(['status' => 'completed']);
    }

    /** Offline proof approved: complete the agent's pending reservation. */
    public function completeForAgent(EnrollmentAgent $agent): void
    {
        LicenseDiscountRedemption::where('agent_id', $agent->id)
            ->where('status', 'pending')
            ->update(['status' => 'completed']);
    }

    /** Offline proof rejected: release the reservation so the agent can retry. */
    public function releaseForAgent(EnrollmentAgent $agent): void
    {
        LicenseDiscountRedemption::where('agent_id', $agent->id)
            ->where('status', 'pending')
            ->delete();
    }
}
