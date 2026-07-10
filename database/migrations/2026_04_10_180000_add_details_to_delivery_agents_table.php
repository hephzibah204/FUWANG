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
        Schema::table('delivery_agents', function (Blueprint $table) {
            $table->string('address')->nullable()->after('city');
            $table->string('phone_number')->nullable()->after('address');
            $table->string('means_of_identification')->nullable()->after('phone_number');
            $table->string('identification_number')->nullable()->after('means_of_identification');
            $table->string('proof_of_address')->nullable()->after('identification_number');
            $table->string('next_of_kin_name')->nullable()->after('proof_of_address');
            $table->string('next_of_kin_phone')->nullable()->after('next_of_kin_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_agents', function (Blueprint $table) {
            $table->dropColumn([
                'address',
                'phone_number',
                'means_of_identification',
                'identification_number',
                'proof_of_address',
                'next_of_kin_name',
                'next_of_kin_phone',
            ]);
        });
    }
};
