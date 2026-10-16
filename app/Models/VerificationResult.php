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

    protected $appends = [
        'resolved_new_tracking_id',
    ];

    /**
     * Robustly extract a newly assigned tracking ID from any response payload or nested structure.
     */
    public static function extractNewTrackingId(mixed $payload, ?string $originalTrackingId = null): ?string
    {
        if (empty($payload)) {
            return null;
        }

        $orig = $originalTrackingId ? trim(strtoupper($originalTrackingId)) : null;

        // If string passed, check regex for tracking ID patterns
        if (is_string($payload)) {
            // Check for explicit "new tracking id: XXX" or "tracking id: XXX"
            if (preg_match('/(?:new\s*(?:tracking\s*(?:id|no|number)?|tid|id)?[:\s=]+)([A-Z0-9]{8,35})/i', $payload, $m)) {
                $candidate = trim(strtoupper($m[1]));
                if (!$orig || $candidate !== $orig) {
                    return $candidate;
                }
            }
            return null;
        }

        if (!is_array($payload)) {
            return null;
        }

        // 1. Direct candidate keys
        $candidateKeys = [
            'new_tracking_id',
            'newTrackingID',
            'newTrackingId',
            'new_tracking',
            'new_tid',
            'newTrackingNumber',
            'new_tracking_number',
            'assigned_tracking_id',
            'cleared_tracking_id',
            'new_nin',
            'newNIN',
            'newNin',
        ];

        foreach ($candidateKeys as $k) {
            if (!empty($payload[$k]) && is_string($payload[$k])) {
                $val = trim($payload[$k]);
                if (strlen($val) >= 7 && (!$orig || strtoupper($val) !== $orig)) {
                    return $val;
                }
            }
        }

        // 2. Check known sub-objects
        $nestedSections = ['data', 'response', 'api_response', 'provider_last_status', 'robosttech_sync', 'payload', 'result'];
        foreach ($nestedSections as $section) {
            if (!empty($payload[$section]) && (is_array($payload[$section]) || is_string($payload[$section]))) {
                $nestedVal = self::extractNewTrackingId($payload[$section], $originalTrackingId);
                if ($nestedVal) {
                    return $nestedVal;
                }
            }
        }

        // 3. Check any "tracking_id" or "trackingID" field if it exists and differs from original
        foreach (['tracking_id', 'trackingID', 'trackingId', 'tracking_no', 'trackingNumber'] as $k) {
            if (!empty($payload[$k]) && is_string($payload[$k])) {
                $val = trim($payload[$k]);
                if ($orig && strtoupper($val) !== $orig && strlen($val) >= 7) {
                    return $val;
                }
            }
        }

        // 4. Check status/message/note text fields for embedded new tracking ID
        foreach (['message', 'admin_note', 'remarks', 'comment', 'detail', 'details'] as $field) {
            if (!empty($payload[$field]) && is_string($payload[$field])) {
                $found = self::extractNewTrackingId($payload[$field], $originalTrackingId);
                if ($found) {
                    return $found;
                }
            }
        }

        return null;
    }

    /**
     * Dynamic attribute to get resolved new tracking ID for this verification record.
     */
    public function getResolvedNewTrackingIdAttribute(): ?string
    {
        $data = $this->response_data ?? [];
        return self::extractNewTrackingId($data, $this->identifier)
            ?: self::extractNewTrackingId($this->admin_note, $this->identifier);
    }

    public function getProviderNameAttribute($value)
    {
        if (request()->is('admin*') || request()->is('api/admin*')) {
            return $value;
        }

        if (stripos((string) $value, 'vuvaa') !== false) {
            return 'Provider 1';
        }
        if (stripos((string) $value, 'dataverify') !== false) {
            return 'Provider 2';
        }

        // Deterministic masking for any other providers
        $hash = crc32((string) $value);
        return 'Provider ' . (($hash % 10) + 3);
    }

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
