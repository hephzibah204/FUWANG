<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VirtualAccountAuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'virtual_account_id',
        'user_id',
        'gateway',
        'action',
        'status',
        'message',
        'context',
        'created_at',
    ];

    protected $casts = [
        'context' => 'array',
        'created_at' => 'datetime',
    ];

    public function virtualAccount()
    {
        return $this->belongsTo(VirtualAccount::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

