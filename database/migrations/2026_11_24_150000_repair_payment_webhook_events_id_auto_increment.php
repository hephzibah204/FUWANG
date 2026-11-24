<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Repair migration (2026-11-24).
 *
 * On production, payment_webhook_events.id lost its AUTO_INCREMENT
 * (the table was created/restored outside the original migration).
 * Every insert in WebhookController::storeEvent() then failed with
 * SQLSTATE 1364 "Field 'id' doesn't have a default value", so valid
 * Paystack webhooks returned HTTP 500 and were never recorded or
 * processed — wallets were not auto-credited via webhook.
 *
 * This migration restores AUTO_INCREMENT on id. It is guarded and
 * idempotent: on any database where id already auto-increments
 * (including production after the emergency ALTER of 2026-11-24)
 * it is a no-op.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payment_webhook_events')) {
            return;
        }

        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        $column = DB::selectOne(
            "SELECT COLUMN_TYPE, EXTRA, COLUMN_KEY
               FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'payment_webhook_events'
                AND COLUMN_NAME = 'id'"
        );

        if (!$column) {
            return;
        }

        if (str_contains(strtolower((string) $column->EXTRA), 'auto_increment')) {
            return; // Already healthy.
        }

        $type = $column->COLUMN_TYPE ?: 'bigint unsigned';

        if ($column->COLUMN_KEY === 'PRI') {
            DB::statement("ALTER TABLE `payment_webhook_events` MODIFY `id` {$type} NOT NULL AUTO_INCREMENT");
        } else {
            DB::statement("ALTER TABLE `payment_webhook_events` MODIFY `id` {$type} NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`)");
        }
    }

    public function down(): void
    {
        // Repair migration: no rollback.
    }
};
