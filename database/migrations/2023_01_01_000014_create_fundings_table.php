<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('fundings')) {
            return;
        }

        Schema::create('fundings', function (Blueprint $table) {
            $table->id();
            $table->string('funding_type')->nullable();
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->string('email')->index();
            $table->string('fullname')->nullable();
            $table->text('description')->nullable();
            $table->string('reference')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fundings');
    }
};
