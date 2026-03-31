<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsCampaignRecipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'sms_campaign_id',
        'user_id',
        'phone',
        'status',
        'provider_message_id',
        'provider_response',
        'sent_at',
        'delivered_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(SmsCampaign::class, 'sms_campaign_id');
    }
}

