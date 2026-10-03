<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bank_details')) {
            Schema::create('bank_details', function (Blueprint $table) {
                $table->id();
                $table->string('email')->unique();
                $table->string('contract_code')->nullable();
                $table->string('account_reference')->nullable();
                $table->string('account_name')->nullable();
                $table->string('currency_code')->default('NGN');
                $table->string('status')->nullable();
                $table->string('psb9')->nullable();
                $table->string('GTBank_account')->nullable();
                $table->string('Moniepoint_account')->nullable();
                $table->string('Wema_account')->nullable();
                $table->string('Sterling_account')->nullable();
                $table->string('palmpay')->nullable();
                $table->decimal('psb_amount', 18, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_details');
    }
};
