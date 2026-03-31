<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventTicket extends Model
{
    protected $fillable = [
        'user_id',
        'event_name',
        'event_date',
        'ticket_type',
        'quantity',
        'attendee_name',
        'attendee_email',
        'amount_paid',
        'reference',
        'qr_code_path',
        'ticket_pdf_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
