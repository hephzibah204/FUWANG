<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('logistics_centers')) {
            return;
        }

        Schema::create('logistics_centers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->index();
            $table->string('state')->index();
            $table->string('city')->nullable()->index();
            $table->string('address')->nullable();
            $table->decimal('lat', 10, 7)->nullable()->index();
            $table->decimal('lng', 10, 7)->nullable()->index();
            $table->string('availability_status')->default('available')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('capacity_per_day')->nullable();
            $table->unsignedInteger('current_load')->default(0);
            $table->timestamps();

            $table->index(['state', 'type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logistics_centers');
    }
};

