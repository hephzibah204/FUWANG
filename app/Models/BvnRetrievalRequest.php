<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BvnRetrievalRequest extends Model
{
    use HasFactory;

    protected $table = 'bvn_retrieval_requests';

    protected $fillable = [
        'user_id',
        'phone_number',
        'full_name',
        'dob',
        'transaction_id',
        'provider_transaction_id',
        'provider',
        'amount',
        'status',
        'retrieved_bvn',
        'provider_response',
        'failure_reason',
        'completed_at',
        'refunded_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'provider_response' => 'array',
        'completed_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
