<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Comprehensive AUTO_INCREMENT repair (2026-11-24).
 *
 * A production audit on 2026-11-24 found 46 tables whose bigint `id`
 * column had lost both AUTO_INCREMENT and its primary key (restore-era
 * damage; the same defect that silently broke Paystack webhook inserts
 * on payment_webhook_events, repaired separately in
 * 2026_11_24_150000_repair_payment_webhook_events_id_auto_increment).
 *
 * This migration restores AUTO_INCREMENT + PRIMARY KEY on the 42 tables
 * whose existing ids are clean (unique, non-null, non-zero — verified in
 * the pre-flight audit of 2026-11-24). Each table is handled
 * independently: a failure on one is logged and does not block the rest,
 * and the migration no-ops on any table that is already healthy.
 *
 * DELIBERATELY EXCLUDED — these tables hold duplicate/NULL/zero ids and
 * need data reconciliation before a primary key can be added:
 *   - account_balance  (48 rows, every id NULL; legacy table)
 *   - permissions      (29 rows, only 17 distinct ids; spatie — ids are
 *                       referenced by role/model permission pivots)
 *   - roles            (5 rows, only 3 distinct ids; same caution)
 *   - system_settings  (28 rows, 15 rows share id 0)
 */
return new class extends Migration
{
    /** @var string[] */
    private array $tables = [
        'ab_events',
        'api_centers',
        'api_settings',
        'auction_bids',
        'auction_lot_images',
        'auction_lots',
        'auction_sellers',
        'auction_watchlists',
        'bank_details',
        'broadcasts',
        'charges',
        'chatbot_feedbacks',
        'chatbot_knowledge_base',
        'chatbot_sessions',
        'custom_apis',
        'custom_api_verification_types',
        'direct_messages',
        'email_campaign_recipients',
        'email_campaigns',
        'email_preferences',
        'email_webhook_events',
        'event_tickets',
        'failed_jobs',
        'feature_toggles',
        'funding_history',
        'legal_documents',
        'logistics_requests',
        'manual_funding',
        'notary_requests',
        'notary_settings',
        'notifying_centers',
        'pages',
        'payment_gateways',
        'payment_transactions',
        'posts',
        'referral_audit_logs',
        'referrals',
        'service_invoices',
        'settings',
        'sms_campaign_recipients',
        'sms_campaigns',
        'system_metrics',
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach ($this->tables as $table) {
            try {
                $this->repair($table);
            } catch (\Throwable $e) {
                Log::warning("AUTO_INCREMENT repair skipped for {$table}: " . $e->getMessage());
            }
        }
    }

    private function repair(string $table): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $column = DB::selectOne(
            "SELECT COLUMN_TYPE, EXTRA, COLUMN_KEY
               FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = ?
                AND COLUMN_NAME = 'id'",
            [$table]
        );

        if (!$column || str_contains(strtolower((string) $column->EXTRA), 'auto_increment')) {
            return; // Missing column or already healthy.
        }

        $type = $column->COLUMN_TYPE ?: 'bigint unsigned';

        if ($column->COLUMN_KEY === 'PRI') {
            DB::statement("ALTER TABLE `{$table}` MODIFY `id` {$type} NOT NULL AUTO_INCREMENT");
        } else {
            DB::statement("ALTER TABLE `{$table}` MODIFY `id` {$type} NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`)");
        }
    }

    public function down(): void
    {
        // Repair migration: no rollback.
    }
};
