<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notifying_centers')) {
            Schema::create('notifying_centers', function (Blueprint $table) {
                $table->id();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('whatsapp')->nullable();
                $table->string('telegram')->nullable();
                $table->boolean('email_enabled')->default(false);
                $table->boolean('sms_enabled')->default(false);
                $table->boolean('whatsapp_enabled')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('verification_prices')) {
            Schema::create('verification_prices', function (Blueprint $table) {
                $table->id();
                $table->string('service_type')->unique();
                $table->decimal('price', 12, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('manual_funding')) {
            Schema::create('manual_funding', function (Blueprint $table) {
                $table->id();
                $table->string('bank_name')->nullable();
                $table->string('account_number')->nullable();
                $table->string('account_name')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('payment_gateways')) {
            Schema::create('payment_gateways', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('display_name')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('priority')->default(1);
                $table->string('logo_url')->nullable();
                $table->json('config')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
        Schema::dropIfExists('manual_funding');
        Schema::dropIfExists('verification_prices');
        Schema::dropIfExists('notifying_centers');
    }
};
