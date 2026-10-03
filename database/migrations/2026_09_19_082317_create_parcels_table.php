<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('parcels', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_number')->unique();
            $table->foreignId('courier_id')->constrained('parcel_couriers')->onDelete('cascade');
            $table->foreignId('shop_id')->constrained('parcel_shops')->onDelete('cascade');
            $table->string('status')->index(); // customer_dropped_off, driver_collected, driver_dropped_off, customer_collected, rejected, damaged
            $table->string('condition')->default('good');
            $table->json('sender_data')->nullable();
            $table->json('receiver_data')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parcels');
    }
};
