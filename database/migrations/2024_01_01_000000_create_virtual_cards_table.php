<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('virtual_cards')) {
            Schema::create('virtual_cards', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('card_name')->nullable();
                $table->string('card_number')->nullable();
                $table->string('expiry_date')->nullable();
                $table->string('cvv')->nullable();
                $table->string('currency')->default('USD');
                $table->decimal('balance', 18, 2)->default(0);
                $table->string('status')->default('active')->index();
                $table->string('reference')->nullable();
                $table->string('provider_card_id')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('virtual_cards');
    }
};
