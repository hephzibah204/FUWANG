<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->index(['user_email', 'created_at'], 'idx_transactions_user_email_created_at');
                $table->index(['user_email', 'transaction_id'], 'idx_transactions_user_email_transaction_id');
            });

            Schema::table('transactions', function (Blueprint $table) {
                $table->dropIndex('idx_transactions_user_email');
            });
        }

        if (Schema::hasTable('verification_results')) {
            Schema::table('verification_results', function (Blueprint $table) {
                $table->index(
                    ['service_type', 'status', 'created_at'],
                    'idx_verification_results_service_status_created_at'
                );
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('verification_results')) {
            Schema::table('verification_results', function (Blueprint $table) {
                $table->dropIndex('idx_verification_results_service_status_created_at');
            });
        }

        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->index('user_email', 'idx_transactions_user_email');
            });

            Schema::table('transactions', function (Blueprint $table) {
                $table->dropIndex('idx_transactions_user_email_created_at');
                $table->dropIndex('idx_transactions_user_email_transaction_id');
            });
        }
    }
};
