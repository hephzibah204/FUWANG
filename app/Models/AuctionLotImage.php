<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuctionLotImage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'auction_lot_id',
        'url',
        'sort_order',
    ];

    public function lot()
    {
        return $this->belongsTo(AuctionLot::class, 'auction_lot_id');
    }
}
