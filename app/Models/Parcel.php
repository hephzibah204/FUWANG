<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Parcel extends Model
{
    protected $fillable = [
        'tracking_number',
        'courier_id',
        'shop_id',
        'status',
        'condition',
        'sender_data',
        'receiver_data',
        'weight',
        'price',
    ];

    protected $casts = [
        'sender_data' => 'array',
        'receiver_data' => 'array',
        'weight' => 'decimal:2',
        'price' => 'decimal:2',
    ];

    public function courier()
    {
        return $this->belongsTo(ParcelCourier::class);
    }

    public function shop()
    {
        return $this->belongsTo(ParcelShop::class);
    }

    public function custodyEvents()
    {
        return $this->hasMany(ParcelCustodyEvent::class);
    }
}
