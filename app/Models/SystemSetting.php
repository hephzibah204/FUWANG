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

    protected static function booted(): void
    {
        static::creating(function (SystemSetting $setting) {
            if (empty($setting->id)) {
                try {
                    $maxId = (int) (static::query()->max('id') ?? 0);
                    $setting->id = $maxId + 1;
                } catch (\Throwable) {
                    $setting->id = (int) (hrtime(true) % 2147483647);
                }
            }
        });
    }

    /**
     * Override insertAndSetId to support legacy tables lacking AUTO_INCREMENT
     */
    protected function insertAndSetId(\Illuminate\Database\Eloquent\Builder $query, $attributes)
    {
        $keyName = $this->getKeyName();
        $assignedId = $attributes[$keyName] ?? $this->getAttribute($keyName);

        try {
            $id = $query->insertGetId($attributes, $keyName);
        } catch (\Throwable $e) {
            if (empty($attributes[$keyName])) {
                $maxId = (int) (static::query()->max($keyName) ?? 0);
                $attributes[$keyName] = $maxId + 1;
            }
            $query->insert($attributes);
            $id = $attributes[$keyName];
        }

        $finalId = (!empty($id) && (int) $id > 0) ? $id : $assignedId;
        $this->setAttribute($keyName, $finalId);
    }

    public static function ensureTableExists(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        try {
            if (!Schema::hasTable('system_settings')) {
                Schema::create('system_settings', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->id();
                    $table->string('key')->unique();
                    $table->longText('value')->nullable();
                    $table->string('type', 50)->nullable();
                    $table->string('group', 80)->nullable()->index();
                    $table->string('label')->nullable();
                    $table->timestamps();
                });
            } else {
                $hasGroup = Schema::hasColumn('system_settings', 'group');
                $hasType = Schema::hasColumn('system_settings', 'type');
                $hasLabel = Schema::hasColumn('system_settings', 'label');

                if (!$hasGroup || !$hasType || !$hasLabel) {
                    Schema::table('system_settings', function (\Illuminate\Database\Schema\Blueprint $table) use ($hasGroup, $hasType, $hasLabel) {
                        if (!$hasGroup) {
                            $table->string('group', 80)->nullable()->index();
                        }
                        if (!$hasType) {
                            $table->string('type', 50)->nullable();
                        }
                        if (!$hasLabel) {
                            $table->string('label')->nullable();
                        }
                    });
                }
            }
            static::$hasTableCache = true;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('SystemSetting::ensureTableExists notice: ' . $e->getMessage());
        }
    }

    public static function clearLocalCache(): void
    {
        static::$hasTableCache = null;
        static::$localCache = null;
    }

    protected static function loadAll(): void
    {
        if (static::$localCache !== null && !empty(static::$localCache)) {
            return;
        }

        static::$hasTableCache = Schema::hasTable((new static())->getTable());

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

        if ($value === null || $value === '') {
            return $default;
        }

        if (is_numeric($value)) {
            return $value + 0;
        }

        return $value;
    }

    public static function put(string $key, mixed $value, ?string $group = null, ?string $type = null, ?string $label = null): self
    {
        static::ensureTableExists();

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
        Cache::forget('whatsapp_widget_config');
        static::$localCache = null;
        static::$hasTableCache = null;

        $payload = [
            'value' => is_scalar($value) ? (string) $value : json_encode($value),
        ];

        $hasGroup = false;
        $hasType = false;
        $hasLabel = false;
        try {
            $hasGroup = Schema::hasColumn('system_settings', 'group');
            $hasType = Schema::hasColumn('system_settings', 'type');
            $hasLabel = Schema::hasColumn('system_settings', 'label');
        } catch (\Throwable) {}

        if ($group !== null && $hasGroup) {
            $payload['group'] = $group;
        }
        if ($type !== null && $hasType) {
            $payload['type'] = $type;
        }
        if ($label !== null && $hasLabel) {
            $payload['label'] = $label;
        }

        try {
            return static::query()->updateOrCreate(['key' => $key], $payload);
        } catch (\Throwable) {
            try {
                $simplePayload = ['value' => is_scalar($value) ? (string) $value : json_encode($value)];
                return static::query()->updateOrCreate(['key' => $key], $simplePayload);
            } catch (\Throwable) {
                return new static();
            }
        }
    }

    public static function set(string $key, mixed $value, ?string $group = null, ?string $type = null, ?string $label = null): self
    {
        return static::put($key, $value, $group, $type, $label);
    }
}
