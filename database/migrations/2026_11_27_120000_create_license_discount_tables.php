<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * License discount codes (owner request 2026-11-27): admins create
     * percentage codes; agents apply them at license checkout. A code can
     * be redeemed at most once per user (unique pair below).
     */
    public function up(): void
    {
        if (!Schema::hasTable('license_discount_codes')) {
            Schema::create('license_discount_codes', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->unsignedTinyInteger('percent');
                $table->unsignedInteger('max_uses')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('license_discount_redemptions')) {
            Schema::create('license_discount_redemptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('license_discount_code_id')->constrained('license_discount_codes')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('agent_id')->constrained('enrollment_agents')->cascadeOnDelete();
                $table->string('reference', 120)->nullable()->index();
                $table->decimal('amount_saved', 12, 2)->default(0);
                $table->string('status', 20)->default('pending'); // pending | completed
                $table->timestamps();
                $table->unique(['license_discount_code_id', 'user_id'], 'license_discount_user_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('license_discount_redemptions');
        Schema::dropIfExists('license_discount_codes');
    }
};
