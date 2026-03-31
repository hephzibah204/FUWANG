<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppClickLog extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_click_logs';

    protected $fillable = [
        'user_id',
        'page_url',
        'ip_address',
        'user_agent',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
