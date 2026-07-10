<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('feature_toggles')) {
            return;
        }

        Schema::create('feature_toggles', function (Blueprint $table) {
            $table->id();
            $table->string('feature_name')->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->string('offline_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_toggles');
    }
};

