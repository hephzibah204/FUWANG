<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            return;
        }

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('fullname')->nullable();
            $table->string('number')->nullable();
            $table->string('role')->default('user')->index();
            $table->string('username', 60)->nullable()->unique();
            $table->string('email')->unique();
            $table->string('google_id')->nullable()->unique();
            $table->text('google_avatar')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('transaction_pin', 4)->nullable();
            $table->string('reseller_id')->nullable();
            $table->string('referral_id')->nullable();
            $table->unsignedBigInteger('referred_user_id')->nullable()->index();
            $table->string('online_status')->default('offline')->index();
            $table->string('user_status')->default('active')->index();
            $table->string('kyc_tier')->nullable();
            $table->text('kyc_rejection_reason')->nullable();
            $table->json('completed_tours')->nullable();
            $table->string('api_access_status')->default('none')->index();
            $table->json('api_application_details')->nullable();
            $table->text('google2fa_secret')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
