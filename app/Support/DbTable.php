<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DbTable
{
    public static function isBaseTable(string $table): bool
    {
        try {
            $row = DB::selectOne(
                'SELECT TABLE_TYPE AS table_type FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1',
                [$table]
            );
            return $row && isset($row->table_type) && strtoupper((string) $row->table_type) === 'BASE TABLE';
        } catch (\Throwable) {
            return true;
        }
    }

    public static function resolveAccountBalanceTable(): ?string
    {
        if (Schema::hasTable('account_balances')) {
            return 'account_balances';
        }
        if (Schema::hasTable('account_balance')) {
            return 'account_balance';
        }
        return null;
    }
}

