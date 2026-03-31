<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankDetail extends Model
{
    protected $fillable = [
        'contract_code',
        'account_reference',
        'account_name',
        'currency_code',
        'email',
        'status',
        'psb9',
        'GTBank_account',
        'Moniepoint_account',
        'Wema_account',
        'Sterling_account',
        'palmpay',
        'psb_amount',
    ];
}
