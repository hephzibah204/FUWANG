<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('auction_admin_audit_logs')) {
            return;
        }

        Schema::create('auction_admin_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auction_admin_id')->nullable()->constrained('auction_admins')->nullOnDelete();
            $table->string('action');
            $table->json('meta')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['auction_admin_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_admin_audit_logs');
    }
};

