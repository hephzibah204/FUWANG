<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceInvoice extends Model
{
    protected $fillable = [
        'user_id',
        'client_name',
        'client_email',
        'items',
        'subtotal',
        'tax_amount',
        'total_amount',
        'due_date',
        'status',
        'invoice_number',
        'pdf_path',
    ];

    protected $casts = [
        'items' => 'array',
        'due_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
