<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_centers', function (Blueprint $table) {
            $table->string('active_mailer')->default('hostinger')->after('resend_api_key');
            $table->string('mailtrap_host')->nullable()->after('active_mailer');
            $table->string('mailtrap_port')->nullable()->after('mailtrap_host');
            $table->string('mailtrap_username')->nullable()->after('mailtrap_port');
            $table->string('mailtrap_password')->nullable()->after('mailtrap_username');
        });
    }

    public function down(): void
    {
        Schema::table('api_centers', function (Blueprint $table) {
            $table->dropColumn([
                'active_mailer',
                'mailtrap_host',
                'mailtrap_port',
                'mailtrap_username',
                'mailtrap_password'
            ]);
        });
    }
};
