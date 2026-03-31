<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'custom_api_id',
        'name',
        'sender_id',
        'message',
        'audience_type',
        'audience',
        'status',
        'batch_id',
        'recipient_count',
        'delivered_count',
        'failed_count',
        'sent_at',
    ];

    protected $casts = [
        'audience' => 'array',
        'sent_at' => 'datetime',
    ];

    public function recipients()
    {
        return $this->hasMany(SmsCampaignRecipient::class);
    }
}
