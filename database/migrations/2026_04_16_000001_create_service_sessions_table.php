<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('service', 50);
            $table->string('token', 64);
            $table->json('scopes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['token', 'service']);
            $table->index(['user_id', 'service']);
            $table->index(['service', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_sessions');
    }
};