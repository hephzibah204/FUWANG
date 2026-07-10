<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * VTU data bundle catalogue (used by services/vtu/data and VTUController::dataIndex).
     */
    public function up(): void
    {
        if (Schema::hasTable('price_list')) {
            return;
        }

        Schema::create('price_list', function (Blueprint $table) {
            $table->id();
            $table->string('network', 32);
            $table->string('data_plan');
            $table->decimal('amount', 12, 2);
            $table->string('validate')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_list');
    }
};
