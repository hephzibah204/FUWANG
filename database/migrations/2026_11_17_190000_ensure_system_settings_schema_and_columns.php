<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('system_settings')) {
            Schema::create('system_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->longText('value')->nullable();
                $table->string('type', 50)->nullable();
                $table->string('group', 80)->nullable()->index();
                $table->string('label')->nullable();
                $table->timestamps();
            });
            return;
        }

        $hasGroup = Schema::hasColumn('system_settings', 'group');
        $hasType = Schema::hasColumn('system_settings', 'type');
        $hasLabel = Schema::hasColumn('system_settings', 'label');

        if (!$hasGroup || !$hasType || !$hasLabel) {
            Schema::table('system_settings', function (Blueprint $table) use ($hasGroup, $hasType, $hasLabel) {
                if (!$hasGroup) {
                    $table->string('group', 80)->nullable()->index();
                }
                if (!$hasType) {
                    $table->string('type', 50)->nullable();
                }
                if (!$hasLabel) {
                    $table->string('label')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        // Settings data is retained.
    }
};
