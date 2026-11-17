<?php

namespace App\Models;

use App\Services\DataVerify\DataVerifyClient;
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

    public function getConfigAttribute($value): array
    {
        return $this->decodeArrayAttribute($value);
    }

    public function getHeadersAttribute($value): array
    {
        return $this->decodeArrayAttribute($value);
    }

    private function decodeArrayAttribute($value): array
    {
        // Older forms stored JSON strings through Eloquent's JSON cast twice.
        for ($depth = 0; $depth < 2 && is_string($value); $depth++) {
            $value = json_decode($value, true);
        }
        return is_array($value) ? $value : [];
    }

    public function verificationTypes()
    {
        return $this->hasMany(CustomApiVerificationType::class);
    }

    public function getEndpointAttribute(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        return DataVerifyClient::normalizeDomain($value);
    }

    public function setEndpointAttribute(?string $value): void
    {
        $this->attributes['endpoint'] = ($value !== null && $value !== '')
            ? DataVerifyClient::normalizeDomain($value)
            : $value;
    }

    public function getDisplayBandAttribute(): string
    {
        if (request()->is('admin*') || request()->is('api/admin*')) {
            return (string) $this->name;
        }

        return \App\Support\NetworkBandResolver::resolve($this);
    }
}
