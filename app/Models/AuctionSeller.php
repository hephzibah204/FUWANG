<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuctionSeller extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'location',
        'rating',
        'reviews_count',
        'verified',
        'avatar_url',
        'about',
    ];

    protected $casts = [
        'verified' => 'boolean',
    ];

    public function lots()
    {
        return $this->hasMany(AuctionLot::class, 'seller_id');
    }
}
