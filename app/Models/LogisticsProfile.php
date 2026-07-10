<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogisticsProfile extends Model
{
    protected $fillable = [
        'user_id',
        'company_name',
        'contact_person',
        'phone',
        'alternate_phone',
        'email',
        'address',
        'city',
        'state',
        'business_type',
        'preferred_delivery',
        'notification_preferences',
        'is_active',
    ];

    protected $casts = [
        'notification_preferences' => 'array',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shipments()
    {
        return $this->hasMany(LogisticsRequest::class, 'user_id', 'user_id');
    }
}