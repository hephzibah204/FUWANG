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
                $table->index(['status', 'created_at'], 'idx_transactions_status_created_at');
                $table->index('user_email', 'idx_transactions_user_email');
                $table->index('transaction_id', 'idx_transactions_transaction_id');
                $table->index('order_type', 'idx_transactions_order_type');
            });
        }

        if (Schema::hasTable('verification_results')) {
            Schema::table('verification_results', function (Blueprint $table) {
                $table->index(['status', 'created_at'], 'idx_verification_results_status_created_at');
                $table->index(['user_id', 'service_type', 'created_at'], 'idx_verification_results_user_service_created');
                $table->index('reference_id', 'idx_verification_results_reference_id');
            });
        }

        if (Schema::hasTable('logistics_requests')) {
            Schema::table('logistics_requests', function (Blueprint $table) {
                $table->index('tracking_id', 'idx_logistics_requests_tracking_id');
                $table->index(['user_id', 'created_at'], 'idx_logistics_requests_user_created_at');
                $table->index(['status', 'created_at'], 'idx_logistics_requests_status_created_at');
            });
        }

        if (Schema::hasTable('delivery_agents')) {
            Schema::table('delivery_agents', function (Blueprint $table) {
                $table->index(['approval_status', 'created_at'], 'idx_delivery_agents_approval_created_at');
                $table->index(['availability_status', 'created_at'], 'idx_delivery_agents_availability_created_at');
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('email', 'idx_users_email');
                $table->index('created_at', 'idx_users_created_at');
                $table->index(['role', 'created_at'], 'idx_users_role_created_at');
            });
        }

        if (Schema::hasTable('chatbot_sessions')) {
            Schema::table('chatbot_sessions', function (Blueprint $table) {
                $table->index('session_id', 'idx_chatbot_sessions_session_id');
                $table->index(['user_id', 'status', 'created_at'], 'idx_chatbot_sessions_user_status_created');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropIndex('idx_transactions_status_created_at');
                $table->dropIndex('idx_transactions_user_email');
                $table->dropIndex('idx_transactions_transaction_id');
                $table->dropIndex('idx_transactions_order_type');
            });
        }

        if (Schema::hasTable('verification_results')) {
            Schema::table('verification_results', function (Blueprint $table) {
                $table->dropIndex('idx_verification_results_status_created_at');
                $table->dropIndex('idx_verification_results_user_service_created');
                $table->dropIndex('idx_verification_results_reference_id');
            });
        }

        if (Schema::hasTable('logistics_requests')) {
            Schema::table('logistics_requests', function (Blueprint $table) {
                $table->dropIndex('idx_logistics_requests_tracking_id');
                $table->dropIndex('idx_logistics_requests_user_created_at');
                $table->dropIndex('idx_logistics_requests_status_created_at');
            });
        }

        if (Schema::hasTable('delivery_agents')) {
            Schema::table('delivery_agents', function (Blueprint $table) {
                $table->dropIndex('idx_delivery_agents_approval_created_at');
                $table->dropIndex('idx_delivery_agents_availability_created_at');
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('idx_users_email');
                $table->dropIndex('idx_users_created_at');
                $table->dropIndex('idx_users_role_created_at');
            });
        }

        if (Schema::hasTable('chatbot_sessions')) {
            Schema::table('chatbot_sessions', function (Blueprint $table) {
                $table->dropIndex('idx_chatbot_sessions_session_id');
                $table->dropIndex('idx_chatbot_sessions_user_status_created');
            });
        }
    }
};
