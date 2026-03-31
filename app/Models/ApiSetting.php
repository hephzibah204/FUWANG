<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApiSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'nin_search_type',
        'bvn_search_type',
        'data_api_type',
        'airtime_api_type',
        'date',
    ];
}
