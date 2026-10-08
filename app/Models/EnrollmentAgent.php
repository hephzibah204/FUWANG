<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnrollmentAgent extends Model
{
    use HasFactory;

    protected $table = 'enrollment_agents';

    protected $fillable = [
        'user_id',
        'agent_type',
        'company_agent_code',
        'is_fast_tracked',
        'full_name',
        'phone_number',
        'state',
        'residential_address',
        'office_address',
        'bvn',
        'nin',
        'nin_verified',
        'nin_verification_meta',
        'nin_server_status',
        'has_machine',
        'machine_imei',
        'utility_bill_path',
        'picture_path',
        'business_registration_number',
        'business_registration_doc_path',
        'status',
        'rejection_reason',
        'accepted_terms',
        'terms_accepted_at',
        'onboarding_step',
        'total_enrollments',
        'monthly_enrollments',
        'is_mva_of_month',
        'meta',
        'approved_at',
        // License accreditation fields
        'license_status',
        'license_fee_amount',
        'license_fee_paid',
        'license_payment_method',
        'license_payment_reference',
        'license_proof_path',
        'license_proof_meta',
        'license_paid_at',
        'license_verified_by',
        'license_verified_at',
        'license_rejection_reason',
        'license_admin_notes',
    ];

    protected $casts = [
        'nin_verified' => 'boolean',
        'nin_verification_meta' => 'array',
        'has_machine' => 'boolean',
        'accepted_terms' => 'boolean',
        'is_fast_tracked' => 'boolean',
        'is_mva_of_month' => 'boolean',
        'meta' => 'array',
        'approved_at' => 'datetime',
        'terms_accepted_at' => 'datetime',
        'total_enrollments' => 'integer',
        'monthly_enrollments' => 'integer',
        'license_fee_amount' => 'decimal:2',
        'license_fee_paid' => 'decimal:2',
        'license_proof_meta' => 'array',
        'license_paid_at' => 'datetime',
        'license_verified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function licenseVerifiedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'license_verified_by');
    }

    public function licenseTransactions()
    {
        return $this->hasMany(AgentLicenseTransaction::class, 'agent_id')->latest();
    }

    public function isLicensePaid(): bool
    {
        return in_array($this->license_status, ['paid', 'waived'], true);
    }

    public function isLicensePendingReview(): bool
    {
        return $this->license_status === 'pending_review';
    }

    public function isLicenseUnpaid(): bool
    {
        return empty($this->license_status) || $this->license_status === 'unpaid';
    }

    public static function isPromoActive(): bool
    {
        $promoEndsAt = SystemSetting::get('agent_license_promo_ends_at', '2026-10-10 23:59:59');
        try {
            return now()->lessThanOrEqualTo(\Carbon\Carbon::parse($promoEndsAt));
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function getEffectiveLicenseFee(): float
    {
        if (self::isPromoActive()) {
            return (float) SystemSetting::get('agent_license_promo_price', 100000.00);
        }

        return (float) SystemSetting::get('agent_license_regular_price', 150000.00);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isFullyActivated(): bool
    {
        return $this->isApproved() && ! empty($this->picture_path);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isExistingAgent(): bool
    {
        return $this->agent_type === 'existing';
    }

    public function isNewAgent(): bool
    {
        return $this->agent_type === 'new';
    }

    public function isOnboardingSubmitted(): bool
    {
        return $this->onboarding_step === 'submitted';
    }

    public function incrementEnrollmentCount(int $count = 1): void
    {
        $this->increment('total_enrollments', $count);
        $this->increment('monthly_enrollments', $count);
        static::recalculateMva();
    }

    /**
     * Automatically recalculate and assign MVP/MVA based on highest total_enrollments.
     * The approved agent with the highest total_enrollments (> 0) is awarded MVP status.
     */
    public static function recalculateMva(): ?self
    {
        $topAgent = static::where('status', 'approved')
            ->where('total_enrollments', '>', 0)
            ->orderByDesc('total_enrollments')
            ->orderByDesc('monthly_enrollments')
            ->first();

        if ($topAgent) {
            // Clear flag on all other agents
            static::query()
                ->where('id', '!=', $topAgent->id)
                ->where('is_mva_of_month', true)
                ->update(['is_mva_of_month' => false]);

            // Ensure top agent is flagged in DB and memory
            static::query()
                ->where('id', $topAgent->id)
                ->update(['is_mva_of_month' => true]);

            $topAgent->is_mva_of_month = true;
        } else {
            static::query()
                ->where('is_mva_of_month', true)
                ->update(['is_mva_of_month' => false]);
        }

        return $topAgent;
    }

    public function getHealthScore(): int
    {
        $score = 0;
        if ($this->isApproved()) {
            $score += 40;
        }
        if (!empty($this->picture_path)) {
            $score += 25;
        }
        if ($this->nin_verified) {
            $score += 25;
        }
        if (!empty($this->machine_imei)) {
            $score += 10;
        }
        return $score;
    }

    public function getCurrentTier(): array
    {
        $count = (int) $this->monthly_enrollments;
        if ($count >= 120) {
            return ['name' => 'Platinum Tier', 'badge' => 'bg-danger', 'color' => '#f43f5e', 'bonus' => '15% Bonus + MVA Nominee', 'next' => 'Max Level', 'needed' => 0];
        }
        if ($count >= 75) {
            return ['name' => 'Gold Tier', 'badge' => 'bg-warning text-dark', 'color' => '#eab308', 'bonus' => '10% Commission Bonus', 'next' => 'Platinum Tier', 'needed' => 120 - $count];
        }
        if ($count >= 30) {
            return ['name' => 'Silver Tier', 'badge' => 'bg-info text-dark', 'color' => '#06b6d4', 'bonus' => '5% Commission Bonus', 'next' => 'Gold Tier', 'needed' => 75 - $count];
        }
        return ['name' => 'Bronze Tier', 'badge' => 'bg-secondary', 'color' => '#94a3b8', 'bonus' => 'Standard Commission', 'next' => 'Silver Tier', 'needed' => 30 - $count];
    }
}
