<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payment_gateways')) {
            Schema::create('payment_gateways', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('display_name')->nullable();
                $table->boolean('is_active')->default(true);
                $table->json('config')->nullable();
                $table->integer('priority')->default(0);
                $table->string('logo_url')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
