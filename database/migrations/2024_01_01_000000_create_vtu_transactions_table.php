<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('vtu_transactions')) {
            Schema::create('vtu_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->index();
                $table->unsignedBigInteger('custom_api_id')->nullable()->index();
                $table->string('service_type')->index();
                $table->string('direction')->default('debit');
                $table->decimal('amount', 18, 2)->default(0);
                $table->decimal('fee', 18, 2)->default(0);
                $table->decimal('total', 18, 2)->default(0);
                $table->string('transaction_id')->unique();
                $table->string('status')->default('pending')->index();
                $table->json('request_payload')->nullable();
                $table->json('response_payload')->nullable();
                $table->string('provider_reference')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vtu_transactions');
    }
};
