<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountBalance extends Model
{
    use HasFactory;

    protected $table = 'account_balances';

    protected $fillable = [
        'user_id',
        'email',
        'user_balance',
        'api_key',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
