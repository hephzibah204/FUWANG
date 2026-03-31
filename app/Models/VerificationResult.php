<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerificationResult extends Model
{
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
