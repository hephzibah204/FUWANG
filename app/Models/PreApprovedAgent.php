<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreApprovedAgent extends Model
{
    use HasFactory;

    protected $table = 'pre_approved_agents';

    protected $fillable = [
        'agent_code',
        'first_name',
        'last_name',
        'full_name',
        'email',
        'phone_number',
        'is_claimed',
        'claimed_at',
        'claimed_by_user_id',
    ];

    protected $casts = [
        'is_claimed' => 'boolean',
        'claimed_at' => 'datetime',
    ];

    public function claimedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by_user_id');
    }

    public function getFormattedPhoneAttribute(): string
    {
        $raw = (string) $this->phone_number;
        if (strlen($raw) === 10 && !str_starts_with($raw, '0')) {
            return '0' . $raw;
        }
        return $raw;
    }
}
