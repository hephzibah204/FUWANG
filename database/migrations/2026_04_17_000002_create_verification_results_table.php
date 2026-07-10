<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('verification_results')) {
            return;
        }

        Schema::create('verification_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('service_type', 100)->index();
            $table->string('identifier', 191)->index();
            $table->string('provider_name', 191)->nullable()->index();
            $table->json('response_data')->nullable();
            $table->string('status', 50)->default('pending')->index();
            $table->string('reference_id', 191)->nullable()->index();
            $table->text('report_path')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_results');
    }
};

