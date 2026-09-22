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
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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
    }
}
