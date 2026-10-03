<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('verification_prices')) {
            Schema::create('verification_prices', function (Blueprint $table) {
                $table->id();
                $table->decimal('nin_by_nin_price', 18, 2)->default(100.00);
                $table->decimal('nin_by_number_price', 18, 2)->default(100.00);
                $table->decimal('nin_by_demography_price', 18, 2)->default(100.00);
                $table->decimal('bvn_by_bvn', 18, 2)->default(100.00);
                $table->decimal('bvn_by_number', 18, 2)->default(100.00);
                $table->decimal('verify_by_tracking_id', 18, 2)->default(100.00);
                $table->decimal('validation_price', 18, 2)->default(100.00);
                $table->decimal('ipe_clearance_price', 18, 2)->default(100.00);
                $table->decimal('personalization_price', 18, 2)->default(100.00);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_prices');
    }
};
