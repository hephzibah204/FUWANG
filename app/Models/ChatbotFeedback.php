<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotFeedback extends Model
{
    use HasFactory;

    protected $table = 'chatbot_feedbacks';

    protected $fillable = [
        'chatbot_session_id',
        'query_text',
        'response_text',
        'is_accurate', // bool
        'score',       // 1-5 rating
        'user_comments',
        'requires_retraining', // flag for continuous learning
    ];

    protected $casts = [
        'is_accurate' => 'boolean',
        'requires_retraining' => 'boolean',
    ];

    public function session()
    {
        return $this->belongsTo(ChatbotSession::class, 'chatbot_session_id');
    }
}
