<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentLicenseTransaction extends Model
{
    use HasFactory;

    protected $table = 'agent_license_transactions';

    protected $fillable = [
        'agent_id',
        'user_id',
        'amount',
        'payment_method',
        'reference',
        'gateway_reference',
        'proof_path',
        'status',
        'admin_id',
        'meta',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'meta' => 'array',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(EnrollmentAgent::class, 'agent_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
