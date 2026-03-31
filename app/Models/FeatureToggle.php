<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeatureToggle extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static ?array $localCache = null;

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
