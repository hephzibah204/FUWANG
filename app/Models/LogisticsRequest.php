<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogisticsRequest extends Model
{
    protected $fillable = [
        'user_id',
        'sender_name',
        'sender_address',
        'recipient_name',
        'recipient_address',
        'weight',
        'description',
        'delivery_type',
        'amount',
        'tracking_id',
        'status',
        'waybill_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
