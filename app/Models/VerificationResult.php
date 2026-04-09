<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class VerificationResult extends Model
{
    use LogsActivity;

    protected $fillable = [
        'user_id',
        'service_type',
        'identifier',
        'provider_name',
        'response_data',
        'status',
        'reference_id',
        'report_path',
        'admin_note',
    ];

    protected $casts = [
        'response_data' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
