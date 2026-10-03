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
        Schema::table('logistics_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('pickup_shop_id')->nullable()->after('pickup_center_id');
            $table->unsignedBigInteger('dropoff_shop_id')->nullable()->after('dropoff_center_id');
            
            $table->foreign('pickup_shop_id')->references('id')->on('parcel_shops')->onDelete('set null');
            $table->foreign('dropoff_shop_id')->references('id')->on('parcel_shops')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logistics_requests', function (Blueprint $table) {
            $table->dropForeign(['pickup_shop_id']);
            $table->dropForeign(['dropoff_shop_id']);
            $table->dropColumn(['pickup_shop_id', 'dropoff_shop_id']);
        });
    }
};
