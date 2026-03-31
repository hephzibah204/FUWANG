<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotaryRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'document_type',
        'form_data',
        'generated_content',
        'status',
        'draft_pdf_path',
        'final_pdf_path',
        'amount_paid',
        'reference',
        'stamped_at'
    ];

    protected $casts = [
        'form_data' => 'array',
        'amount_paid' => 'decimal:2',
        'stamped_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
