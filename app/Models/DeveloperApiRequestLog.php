<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeveloperApiRequestLog extends Model
{
    protected $fillable = [
        'api_token_id',
        'user_id',
        'endpoint_slug',
        'method',
        'path',
        'status_code',
        'ip_address',
        'declared_website',
        'origin_host',
        'referer_host',
        'user_agent',
        'requested_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
    ];

    public function token()
    {
        return $this->belongsTo(ApiToken::class, 'api_token_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

