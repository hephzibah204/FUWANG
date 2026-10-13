<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('search_keyword_logs')) {
            Schema::create('search_keyword_logs', function (Blueprint $table) {
                $table->id();
                $table->string('keyword', 255)->index();
                $table->string('source', 50)->default('search_hub')->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('visitor_id', 64)->nullable()->index();
                $table->integer('results_count')->default(0);
                $table->string('page', 255)->nullable();
                $table->timestamp('created_at')->useCurrent()->index();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('search_keyword_logs');
    }
};
