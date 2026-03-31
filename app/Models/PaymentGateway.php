<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'is_active',
        'config',
        'priority',
        'logo_url',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array',
        'priority' => 'integer',
    ];

    /**
     * Scope a query to only include active payment gateways.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('priority', 'asc');
    }
}
