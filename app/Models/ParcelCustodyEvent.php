<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParcelCustodyEvent extends Model
{
    protected $fillable = [
        'parcel_id',
        'agent_id',
        'event_type',
        'from_entity_type',
        'from_entity_id',
        'to_entity_type',
        'to_entity_id',
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

    public function fromEntity()
    {
        return $this->morphTo('from_entity');
    }

    public function toEntity()
    {
        return $this->morphTo('to_entity');
    }
}
