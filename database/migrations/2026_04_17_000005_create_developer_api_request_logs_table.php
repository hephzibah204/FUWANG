<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('developer_api_request_logs')) {
            return;
        }

        Schema::create('developer_api_request_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('api_token_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('endpoint_slug', 120)->nullable()->index();
            $table->string('method', 10);
            $table->string('path', 255);
            $table->unsignedSmallInteger('status_code')->nullable()->index();
            $table->string('ip_address', 64)->nullable();
            $table->string('declared_website', 255)->nullable();
            $table->string('origin_host', 255)->nullable()->index();
            $table->string('referer_host', 255)->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->timestamp('requested_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('developer_api_request_logs');
    }
};

