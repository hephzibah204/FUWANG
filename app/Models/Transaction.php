<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_email',
        'order_type',
        'balance_before',
        'balance_after',
        'transaction_id',
        'status',
    ];

    protected $casts = [
        'balance_before' => 'float',
        'balance_after' => 'float',
    ];

    /**
     * Get the transaction amount.
     */
    public function getAmountAttribute(): float
    {
        return (float) ($this->balance_after - $this->balance_before);
    }

    /**
     * Get the user that owns the transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_email', 'email');
    }
}
