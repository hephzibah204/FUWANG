<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegalDocument extends Model
{
    protected $fillable = [
        'user_id',
        'document_type',
        'document_name',
        'reference_id',
        'form_data',
        'content',
        'file_path',
        'price',
        'is_stamped',
        'status',
    ];

    protected $casts = [
        'form_data' => 'array',
        'is_stamped' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
