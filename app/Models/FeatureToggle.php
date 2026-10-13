<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeatureToggle extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static ?array $localCache = null;

    protected static function booted(): void
    {
        static::creating(function (FeatureToggle $toggle) {
            if (empty($toggle->id)) {
                try {
                    $maxId = (int) (static::query()->max('id') ?? 0);
                    $toggle->id = $maxId + 1;
                } catch (\Throwable) {
                    $toggle->id = (int) (hrtime(true) % 2147483647);
                }
            }
        });

        static::saved(function (FeatureToggle $toggle) {
            static::clearCache($toggle->feature_name);
        });

        static::deleted(function (FeatureToggle $toggle) {
            static::clearCache($toggle->feature_name);
        });
    }

    /**
     * Override insertAndSetId to support legacy tables lacking AUTO_INCREMENT
     */
    protected function insertAndSetId(\Illuminate\Database\Eloquent\Builder $query, $attributes)
    {
        $keyName = $this->getKeyName();
        $assignedId = $attributes[$keyName] ?? $this->getAttribute($keyName);

        $id = $query->insertGetId($attributes, $keyName);

        $finalId = (!empty($id) && (int) $id > 0) ? $id : $assignedId;
        $this->setAttribute($keyName, $finalId);
    }

    public static function clearCache(?string $featureName = null): void
    {
        static::$localCache = null;
        \Illuminate\Support\Facades\Cache::forget('feature_toggles_all');
        \Illuminate\Support\Facades\Cache::forget('admin:feature_toggles');
        if ($featureName) {
            \Illuminate\Support\Facades\Cache::forget('feature_toggle:' . strtolower($featureName));
        }
    }

    protected static function loadAll(): void
    {
        if (static::$localCache !== null) {
            return;
        }

        try {
            static::$localCache = \Illuminate\Support\Facades\Cache::remember('feature_toggles_all', 3600, function () {
                return static::all()->keyBy(fn($f) => strtolower($f->feature_name))->toArray();
            });
        } catch (\Throwable) {
            static::$localCache = [];
        }
    }

    public static function isActive($featureName)
    {
        static::loadAll();
        $name = strtolower($featureName);

        if (isset(static::$localCache[$name])) {
            return (bool) (static::$localCache[$name]['is_active'] ?? true);
        }

        return true; // Default to true if not explicitly disabled
    }

    public static function getMessage($featureName)
    {
        static::loadAll();
        $name = strtolower($featureName);

        if (isset(static::$localCache[$name])) {
            return static::$localCache[$name]['offline_message'] ?? 'This service is currently unavailable.';
        }

        return 'This service is currently unavailable.';
    }
}
