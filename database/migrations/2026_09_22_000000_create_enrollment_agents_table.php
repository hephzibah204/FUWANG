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
        Schema::create('enrollment_agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('full_name');
            $table->string('phone_number');
            $table->text('residential_address');
            $table->text('office_address');
            $table->string('bvn', 11);
            $table->string('nin', 11);
            $table->boolean('nin_verified')->default(false);
            $table->json('nin_verification_meta')->nullable();
            $table->string('machine_imei')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'suspended'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->unsignedInteger('total_enrollments')->default(0);
            $table->unsignedInteger('monthly_enrollments')->default(0);
            $table->boolean('is_mva_of_month')->default(false);
            $table->json('meta')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_agents');
    }
};
