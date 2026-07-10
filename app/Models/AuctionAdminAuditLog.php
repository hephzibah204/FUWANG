<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuctionAdminAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'auction_admin_id',
        'action',
        'meta',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function auctionAdmin(): BelongsTo
    {
        return $this->belongsTo(AuctionAdmin::class, 'auction_admin_id');
    }
}

