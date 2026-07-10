<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('api_centers')) {
            return;
        }

        Schema::create('api_centers', function (Blueprint $table) {
            $table->id();

            $table->text('dataverify_api_key')->nullable();
            $table->text('dataverify_endpoint_nin')->nullable();
            $table->text('dataverify_endpoint_bvn')->nullable();
            $table->text('dataverify_endpoint_data')->nullable();
            $table->text('dataverify_endpoint_phone')->nullable();
            $table->text('dataverify_endpoint_tid')->nullable();
            $table->text('dataverify_endpoint_premium_slip')->nullable();
            $table->text('dataverify_endpoint_premium_slip_phone')->nullable();
            $table->text('dataverify_endpoint_standard_slip')->nullable();
            $table->text('dataverify_endpoint_regular_slip')->nullable();
            $table->text('dataverify_endpoint_vnin_slip')->nullable();

            $table->text('payvessel_api_key')->nullable();
            $table->text('payvessel_secret_key')->nullable();
            $table->text('paystack_public_key')->nullable();
            $table->text('paystack_secret_key')->nullable();
            $table->text('flutterwave_public_key')->nullable();
            $table->text('flutterwave_secret_key')->nullable();
            $table->text('flutterwave_encryption_key')->nullable();
            $table->text('paypoint_secret_key')->nullable();
            $table->text('paypoint_api_key')->nullable();
            $table->text('paypoint_businessid')->nullable();
            $table->text('paypoint_endpoint')->nullable();
            $table->text('payvessel_endpoint')->nullable();
            $table->text('payvessel_businessid')->nullable();
            $table->text('monnify_api_key')->nullable();
            $table->text('monnify_secret_key')->nullable();
            $table->text('monnify_endpoint_auth')->nullable();
            $table->text('monnify_endpoint_reserve')->nullable();
            $table->text('monnify_contract_code')->nullable();
            $table->text('ade_apikey')->nullable();
            $table->text('ade_endpoint_exam')->nullable();
            $table->text('ade_endpoint_airtime')->nullable();
            $table->text('ade_endpoint_bill')->nullable();
            $table->text('ade_endpoint_data')->nullable();
            $table->text('nexus_notary_key')->nullable();
            $table->text('nexus_logistics_key')->nullable();
            $table->text('nexus_api_secret')->nullable();
            $table->text('robosttech_api_key')->nullable();
            $table->text('robosttech_endpoint_nin')->nullable();
            $table->text('robosttech_endpoint_validation')->nullable();
            $table->text('robosttech_endpoint_clearance')->nullable();
            $table->text('robosttech_endpoint_clearance_status')->nullable();
            $table->text('robosttech_endpoint_personalization')->nullable();
            $table->text('gemini_api_key')->nullable();
            $table->text('sms_ai_key')->nullable();
            $table->text('sms_ai_endpoint')->nullable();
            $table->text('sms_ai_sender')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_centers');
    }
};

