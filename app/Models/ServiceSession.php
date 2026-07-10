<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceSession extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'service',
        'token',
        'scopes',
        'ip_address',
        'user_agent',
        'expires_at',
    ];

    protected $hidden = [
        'token',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    public function scopeForService($query, string $service)
    {
        return $query->where('service', $service);
    }
}