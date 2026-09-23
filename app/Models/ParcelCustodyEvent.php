<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParcelCustodyEvent extends Model
{
    protected $fillable = [
        'parcel_id',
        'agent_id',
        'event_type',
        'notes',
    ];

    public function parcel()
    {
        return $this->belongsTo(Parcel::class);
    }

    public function agent()
    {
        return $this->belongsTo(ParcelAgent::class);
    }
}
