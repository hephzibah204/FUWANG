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
        Schema::create('agent_license_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('enrollment_agents')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 50); // paystack, wallet, offline_proof, admin_manual, legacy_pre_platform, waived
            $table->string('reference', 120)->unique();
            $table->string('gateway_reference', 120)->nullable()->index();
            $table->string('proof_path')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'rejected'])->default('pending')->index();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->json('meta')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_license_transactions');
    }
};
