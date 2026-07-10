<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('logistics_ai_pricing_models')) {
            return;
        }

        Schema::create('logistics_ai_pricing_models', function (Blueprint $table) {
            $table->id();
            $table->string('version')->unique();
            $table->json('feature_keys');
            $table->json('weights');
            $table->decimal('multiplier', 18, 6)->default(0);
            $table->json('metrics')->nullable();
            $table->timestamp('trained_at')->nullable()->index();
            $table->boolean('is_active')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logistics_ai_pricing_models');
    }
};

