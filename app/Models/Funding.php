<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Funding extends Model
{
    protected $fillable = [
        'email',
        'amount',
        'reference',
        'description',
        'funding_type',
        'fullname',
    ];
}
