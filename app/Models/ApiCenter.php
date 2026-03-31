<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApiCenter extends Model
{
    use HasFactory;

    protected $fillable = [
        'dataverify_api_key',
        'dataverify_endpoint_nin',
        'dataverify_endpoint_bvn',
        'dataverify_endpoint_data',
        'dataverify_endpoint_phone',
        'dataverify_endpoint_tid',
        'dataverify_endpoint_premium_slip',
        'dataverify_endpoint_premium_slip_phone',
        'dataverify_endpoint_standard_slip',
        'dataverify_endpoint_regular_slip',
        'dataverify_endpoint_vnin_slip',
        'payvessel_api_key',
        'payvessel_secret_key',
        'paystack_public_key',
        'paystack_secret_key',
        'flutterwave_public_key',
        'flutterwave_secret_key',
        'flutterwave_encryption_key',
        'paypoint_secret_key',
        'paypoint_api_key',
        'paypoint_businessid',
        'paypoint_endpoint',
        'payvessel_endpoint',
        'payvessel_businessid',
        'monnify_api_key',
        'monnify_secret_key',
        'monnify_endpoint_auth',
        'monnify_endpoint_reserve',
        'monnify_contract_code',
        'ade_apikey',
        'ade_endpoint_exam',
        'ade_endpoint_airtime',
        'ade_endpoint_bill',
        'ade_endpoint_data',
        'nexus_notary_key',
        'nexus_logistics_key',
        'nexus_api_secret',
        'robosttech_api_key',
        'robosttech_endpoint_nin',
        'robosttech_endpoint_validation',
        'robosttech_endpoint_clearance',
        'robosttech_endpoint_clearance_status',
        'robosttech_endpoint_personalization',
        'gemini_api_key',
        'sms_ai_key',
        'sms_ai_endpoint',
        'sms_ai_sender',
    ];
}
