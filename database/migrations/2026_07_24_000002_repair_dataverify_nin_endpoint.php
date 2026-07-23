<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('custom_apis')) {
            return;
        }

        DB::table('custom_apis')
            ->where('provider_identifier', 'dataverify')
            ->where(function ($query) {
                $query->where('endpoint', 'like', 'https://api.dataverify.com.ng%')
                    ->orWhere('endpoint', 'like', 'https://api.dataverify.ng%');
            })
            ->update([
                'endpoint' => 'https://dataverify.com.ng/developers/nin_slips/nin_premium',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Do not restore a hostname whose TLS certificate does not match.
    }
};
