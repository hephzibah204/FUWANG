<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SystemSetting extends Model
{
    protected static ?bool $hasTableCache = null;
    protected static ?array $localCache = null;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
    ];

    protected $casts = [
        'value' => 'string', // Handle based on type if needed
    ];

    protected static function loadAll(): void
    {
        if (static::$localCache !== null) {
            return;
        }

        if (static::$hasTableCache === null) {
            try {
                static::$hasTableCache = Schema::hasTable((new static())->getTable());
            } catch (\Throwable) {
                static::$hasTableCache = false;
            }
        }

        if (!static::$hasTableCache) {
            static::$localCache = [];
            return;
        }

        try {
            static::$localCache = Cache::remember('system_settings_all', 3600, function () {
                return static::query()->pluck('value', 'key')->toArray();
            });
        } catch (\Throwable) {
            static::$localCache = [];
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        static::loadAll();

        if (array_key_exists($key, static::$localCache)) {
            $value = static::$localCache[$key];
        } else {
            return $default;
        }

        if ($value === null) {
            return $default;
        }

        if (is_numeric($value)) {
            return $value + 0;
        }

        return $value;
    }

    public static function put(string $key, mixed $value, ?string $group = null, ?string $type = null, ?string $label = null): self
    {
        if (static::$hasTableCache === null) {
            try {
                static::$hasTableCache = Schema::hasTable((new static())->getTable());
            } catch (\Throwable) {
                static::$hasTableCache = false;
            }
        }

        if (!static::$hasTableCache) {
            return new static();
        }

        Cache::forget('system_setting:' . $key);
        Cache::forget('system_settings_all');
        static::$localCache = null;

        $payload = [
            'value' => is_scalar($value) ? (string) $value : json_encode($value),
        ];

        if ($group !== null) {
            $payload['group'] = $group;
        }
        if ($type !== null) {
            $payload['type'] = $type;
        }
        if ($label !== null) {
            $payload['label'] = $label;
        }

        try {
            return static::query()->updateOrCreate(['key' => $key], $payload);
        } catch (\Throwable) {
            return new static();
        }
    }

    public static function set(string $key, mixed $value, ?string $group = null, ?string $type = null, ?string $label = null): self
    {
        return static::put($key, $value, $group, $type, $label);
    }
}
