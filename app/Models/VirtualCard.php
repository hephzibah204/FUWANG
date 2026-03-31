<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VirtualCard extends Model
{
    protected $fillable = [
        'user_id',
        'card_name',
        'card_number',
        'expiry_date',
        'cvv',
        'currency',
        'balance',
        'status',
        'reference',
        'provider_card_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
