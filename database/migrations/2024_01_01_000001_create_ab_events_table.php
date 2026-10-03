<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ab_events')) {
            Schema::create('ab_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('session_id')->nullable();
                $table->string('experiment');
                $table->string('variant');
                $table->string('event_name');
                $table->string('page')->nullable();
                $table->json('meta')->nullable();
                $table->string('ip')->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ab_events');
    }
};
