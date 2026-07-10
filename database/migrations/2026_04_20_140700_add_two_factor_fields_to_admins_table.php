<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('admins')) {
            return;
        }

        Schema::table('admins', function (Blueprint $table) {
            if (!Schema::hasColumn('admins', 'two_factor_enabled')) {
                $table->boolean('two_factor_enabled')->default(false)->after('is_super_admin');
            }

            if (!Schema::hasColumn('admins', 'two_factor_secret')) {
                $table->text('two_factor_secret')->nullable()->after('two_factor_enabled');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('admins')) {
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
        });
    }
};

