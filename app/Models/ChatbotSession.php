<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotSession extends Model
{
    use HasFactory;

    protected $table = 'chatbot_sessions';

    protected $fillable = [
        'user_id',
        'session_id',
        'conversation_history',
        'language',
        'intent_summary',
        'status', // active, handed_off, resolved
    ];

    protected $casts = [
        'conversation_history' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
