<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomApi extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'provider_identifier',
        'service_type',
        'supported_modes',
        'endpoint',
        'api_key',
        'secret_key',
        'headers',
        'config',
        'status',
        'priority',
        'price',
        'timeout_seconds',
        'retry_count',
        'retry_delay_ms',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $maxId = static::max('id');
                $model->id = $maxId ? ((int) $maxId + 1) : 1;
            }
        });
    }

    protected $casts = [
        'headers' => 'array',
        'config' => 'array',
        'supported_modes' => 'array',
        'status' => 'boolean',
        'priority' => 'integer',
    ];

    public function verificationTypes()
    {
        return $this->hasMany(CustomApiVerificationType::class);
    }
}
