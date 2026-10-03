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
                $table->decimal('price', 12, 2)->default(0);
                $table->text('description')->nullable();
                $table->boolean('requires_court_stamp')->default(false);
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('notary_requests')) {
            Schema::create('notary_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('document_type');
                $table->json('form_data')->nullable();
                $table->longText('generated_content')->nullable();
                $table->string('status')->default('pending');
                $table->string('draft_pdf_path')->nullable();
                $table->string('final_pdf_path')->nullable();
                $table->decimal('amount_paid', 12, 2)->default(0);
                $table->string('reference')->nullable();
                $table->timestamp('stamped_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('legal_documents')) {
            Schema::create('legal_documents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('document_type')->nullable();
                $table->string('title')->nullable();
                $table->longText('content')->nullable();
                $table->string('status')->default('draft');
                $table->string('file_path')->nullable();
                $table->decimal('amount_paid', 12, 2)->default(0);
                $table->string('reference')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notary_requests');
        Schema::dropIfExists('notary_settings');
    }
};
