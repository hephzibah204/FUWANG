<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotarySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_type',
        'category',
        'price',
        'stamp_path',
        'signature_path',
        'description',
        'requires_court_stamp'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'requires_court_stamp' => 'boolean'
    ];
}
