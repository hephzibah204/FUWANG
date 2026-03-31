<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuctionLot extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'seller_id',
        'lot_code',
        'title',
        'category',
        'location',
        'description',
        'starting_price',
        'current_price',
        'bid_increment',
        'start_at',
        'end_at',
        'status',
        'featured',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'featured' => 'boolean',
        'starting_price' => 'decimal:2',
        'current_price' => 'decimal:2',
        'bid_increment' => 'decimal:2',
    ];

    public function seller()
    {
        return $this->belongsTo(AuctionSeller::class, 'seller_id');
    }

    public function images()
    {
        return $this->hasMany(AuctionLotImage::class, 'auction_lot_id')->orderBy('sort_order')->orderBy('id');
    }
}
