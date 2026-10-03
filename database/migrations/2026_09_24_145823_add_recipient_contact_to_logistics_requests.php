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
        Schema::table('logistics_requests', function (Blueprint $table) {
            $table->string('recipient_phone')->nullable()->after('recipient_address');
            $table->string('recipient_email')->nullable()->after('recipient_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logistics_requests', function (Blueprint $table) {
            $table->dropColumn(['recipient_phone', 'recipient_email']);
        });
    }
};
