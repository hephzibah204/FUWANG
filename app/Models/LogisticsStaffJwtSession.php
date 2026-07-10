<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogisticsStaffJwtSession extends Model
{
    protected $fillable = [
        'logistics_staff_id',
        'jti',
        'expires_at',
        'revoked_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function staff()
    {
        return $this->belongsTo(LogisticsStaff::class, 'logistics_staff_id');
    }
}

