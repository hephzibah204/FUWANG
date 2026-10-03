<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('virtual_accounts')) {
            Schema::create('virtual_accounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('gateway')->nullable();
                $table->string('account_number')->nullable()->index();
                $table->string('bank_name')->nullable();
                $table->string('account_name')->nullable();
                $table->string('currency')->default('NGN');
                $table->string('status')->default('active')->index();
                $table->string('reference')->nullable();
                $table->string('provider_customer_reference')->nullable();
                $table->string('provider_account_reference')->nullable();
                $table->json('meta')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('activated_at')->nullable();
                $table->timestamp('last_synced_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('virtual_accounts');
    }
};
