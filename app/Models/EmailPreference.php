<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class EmailPreference extends Model
{
    protected $fillable = [
        'user_id',
        'welcome_enabled',
        'login_alerts_enabled',
        'unsubscribed_at',
    ];

    protected $casts = [
        'welcome_enabled' => 'boolean',
        'login_alerts_enabled' => 'boolean',
        'unsubscribed_at' => 'datetime',
    ];

    public static function allowsWelcome(User $user): bool
    {
        if (!Schema::hasTable('email_preferences')) {
            return true;
        }

        $pref = static::query()->where('user_id', $user->id)->first();
        if (!$pref) {
            return true;
        }

        if ($pref->unsubscribed_at !== null) {
            return false;
        }

        return (bool) $pref->welcome_enabled;
    }

    public static function allowsLoginAlert(User $user): bool
    {
        if (!Schema::hasTable('email_preferences')) {
            return true;
        }

        $pref = static::query()->where('user_id', $user->id)->first();
        if (!$pref) {
            return true;
        }

        if ($pref->unsubscribed_at !== null) {
            return false;
        }

        return (bool) $pref->login_alerts_enabled;
    }
}

