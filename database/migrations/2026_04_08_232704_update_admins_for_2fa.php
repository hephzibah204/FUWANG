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
        if (! Schema::hasTable('admins')) {
            return;
        }

        Schema::table('admins', function (Blueprint $table) {
            $drop = [];
            if (Schema::hasColumn('admins', 'two_factor_secret')) {
                $drop[] = 'two_factor_secret';
            }
            if (Schema::hasColumn('admins', 'two_factor_enabled')) {
                $drop[] = 'two_factor_enabled';
            }
            if ($drop) {
                $table->dropColumn($drop);
            }

            if (! Schema::hasColumn('admins', 'google2fa_secret')) {
                $table->text('google2fa_secret')->nullable()->after('is_super_admin');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('admins')) {
            return;
        }

        Schema::table('admins', function (Blueprint $table) {
            if (! Schema::hasColumn('admins', 'two_factor_enabled')) {
                $table->boolean('two_factor_enabled')->default(false);
            }
            if (! Schema::hasColumn('admins', 'two_factor_secret')) {
                $table->text('two_factor_secret')->nullable();
            }
            if (Schema::hasColumn('admins', 'google2fa_secret')) {
                $table->dropColumn('google2fa_secret');
            }
        });
    }
};
