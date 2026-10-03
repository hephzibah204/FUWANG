<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('virtual_account_audit_logs')) {
            Schema::create('virtual_account_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('virtual_account_id')->nullable()->index();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('gateway')->nullable();
                $table->string('action')->index();
                $table->string('status')->index();
                $table->text('message')->nullable();
                $table->json('context')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('virtual_account_audit_logs');
    }
};
