<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('custom_apis')) {
            return;
        }

        Schema::create('custom_apis', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('provider_identifier')->nullable()->index();
            $table->string('service_type')->index();
            $table->json('supported_modes')->nullable();
            $table->text('endpoint')->nullable();
            $table->text('api_key')->nullable();
            $table->text('secret_key')->nullable();
            $table->json('headers')->nullable();
            $table->json('config')->nullable();
            $table->boolean('status')->default(true)->index();
            $table->unsignedInteger('priority')->default(0);
            $table->decimal('price', 15, 2)->nullable();
            $table->unsignedInteger('timeout_seconds')->nullable();
            $table->unsignedInteger('retry_count')->nullable();
            $table->unsignedInteger('retry_delay_ms')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_apis');
    }
};

