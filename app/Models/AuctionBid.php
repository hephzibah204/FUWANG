<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuctionBid extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'lot_id',
        'item_name',
        'bid_amount',
        'status',
        'reference',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
