<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeveloperApiEndpoint extends Model
{
    protected $fillable = [
        'slug',
        'group_name',
        'name',
        'method',
        'path_pattern',
        'is_enabled',
        'docs_summary',
        'docs_request_example',
        'docs_response_example',
        'sort_order',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'sort_order' => 'integer',
    ];
}

