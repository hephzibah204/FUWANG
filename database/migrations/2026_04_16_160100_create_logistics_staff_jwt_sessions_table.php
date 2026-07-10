<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('logistics_staff_jwt_sessions')) {
            return;
        }

        Schema::create('logistics_staff_jwt_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logistics_staff_id')->constrained('logistics_staff')->cascadeOnDelete();
            $table->string('jti', 64)->unique();
            $table->timestamp('expires_at')->index();
            $table->timestamp('revoked_at')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['logistics_staff_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logistics_staff_jwt_sessions');
    }
};

