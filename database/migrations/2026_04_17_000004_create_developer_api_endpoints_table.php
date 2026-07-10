<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('developer_api_endpoints')) {
            return;
        }

        Schema::create('developer_api_endpoints', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 120)->unique();
            $table->string('group_name', 80)->nullable()->index();
            $table->string('name', 160);
            $table->string('method', 10)->default('GET');
            $table->string('path_pattern', 255);
            $table->boolean('is_enabled')->default(true)->index();
            $table->text('docs_summary')->nullable();
            $table->longText('docs_request_example')->nullable();
            $table->longText('docs_response_example')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('developer_api_endpoints');
    }
};

