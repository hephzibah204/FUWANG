<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('fundings')) {
            Schema::table('fundings', function (Blueprint $table) {
                $table->bigIncrements('id')->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('fundings')) {
            Schema::table('fundings', function (Blueprint $table) {
                $table->unsignedBigInteger('id')->change();
            });
        }
    }
};
