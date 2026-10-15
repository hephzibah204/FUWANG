<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'nin_mod_consent')) {
                $table->boolean('nin_mod_consent')->default(false);
            }
            if (!Schema::hasColumn('users', 'nin_mod_consent_at')) {
                $table->timestamp('nin_mod_consent_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'nin_mod_consent')) {
                $table->dropColumn('nin_mod_consent');
            }
            if (Schema::hasColumn('users', 'nin_mod_consent_at')) {
                $table->dropColumn('nin_mod_consent_at');
            }
        });
    }
};
