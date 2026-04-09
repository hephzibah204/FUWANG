<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait Loggable
{
    public function logActivity($action, $description = null)
    {
        $this->activityLogs()->create([
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'loggable');
    }
}
