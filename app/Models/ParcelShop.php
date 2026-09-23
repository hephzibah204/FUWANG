<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParcelShop extends Model
{
    protected $fillable = [
        'name',
        'address',
        'state',
        'city',
        'lat',
        'lng',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
    ];

    public function parcels()
    {
        return $this->hasMany(Parcel::class, 'shop_id');
    }

    public function agents()
    {
        return $this->hasMany(ParcelAgent::class, 'shop_id');
    }
}
