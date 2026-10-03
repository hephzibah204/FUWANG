<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notary_settings')) {
            Schema::create('notary_settings', function (Blueprint $table) {
                $table->id();
                $table->string('document_type')->unique();
                $table->string('category')->nullable();
                $table->decimal('price', 18, 2)->default(0);
                $table->string('stamp_path')->nullable();
                $table->string('signature_path')->nullable();
                $table->text('description')->nullable();
                $table->boolean('requires_court_stamp')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notary_settings');
    }
};
