<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('referral_audit_logs')) {
            return;
        }

        Schema::create('referral_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_id')->nullable()->constrained('referrals', null, 'fk_ref_audit_referral_id')->nullOnDelete()->index('idx_ref_audit_referral_id');
            $table->foreignId('user_id')->constrained('users', null, 'fk_ref_audit_user_id')->cascadeOnDelete()->index('idx_ref_audit_user_id');
            $table->string('action')->index();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['user_id', 'created_at'], 'idx_ref_audit_user_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_audit_logs');
    }
};

