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
        Schema::table('enrollment_agents', function (Blueprint $table) {
            if (!Schema::hasColumn('enrollment_agents', 'license_status')) {
                $table->enum('license_status', ['unpaid', 'pending_review', 'paid', 'waived'])->default('unpaid')->after('status');
            }
            if (!Schema::hasColumn('enrollment_agents', 'license_fee_amount')) {
                $table->decimal('license_fee_amount', 12, 2)->default(100000.00)->after('license_status');
            }
            if (!Schema::hasColumn('enrollment_agents', 'license_fee_paid')) {
                $table->decimal('license_fee_paid', 12, 2)->nullable()->after('license_fee_amount');
            }
            if (!Schema::hasColumn('enrollment_agents', 'license_payment_method')) {
                $table->enum('license_payment_method', [
                    'paystack',
                    'wallet',
                    'offline_proof',
                    'admin_manual',
                    'legacy_pre_platform',
                    'waived'
                ])->nullable()->after('license_fee_paid');
            }
            if (!Schema::hasColumn('enrollment_agents', 'license_payment_reference')) {
                $table->string('license_payment_reference', 120)->nullable()->after('license_payment_method');
            }
            if (!Schema::hasColumn('enrollment_agents', 'license_proof_path')) {
                $table->string('license_proof_path')->nullable()->after('license_payment_reference');
            }
            if (!Schema::hasColumn('enrollment_agents', 'license_proof_meta')) {
                $table->json('license_proof_meta')->nullable()->after('license_proof_path');
            }
            if (!Schema::hasColumn('enrollment_agents', 'license_paid_at')) {
                $table->timestamp('license_paid_at')->nullable()->after('license_proof_meta');
            }
            if (!Schema::hasColumn('enrollment_agents', 'license_verified_by')) {
                $table->foreignId('license_verified_by')->nullable()->constrained('admins')->nullOnDelete()->after('license_paid_at');
            }
            if (!Schema::hasColumn('enrollment_agents', 'license_verified_at')) {
                $table->timestamp('license_verified_at')->nullable()->after('license_verified_by');
            }
            if (!Schema::hasColumn('enrollment_agents', 'license_rejection_reason')) {
                $table->text('license_rejection_reason')->nullable()->after('license_verified_at');
            }
            if (!Schema::hasColumn('enrollment_agents', 'license_admin_notes')) {
                $table->text('license_admin_notes')->nullable()->after('license_rejection_reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollment_agents', function (Blueprint $table) {
            $table->dropForeign(['license_verified_by']);
            $table->dropColumn([
                'license_status',
                'license_fee_amount',
                'license_fee_paid',
                'license_payment_method',
                'license_payment_reference',
                'license_proof_path',
                'license_proof_meta',
                'license_paid_at',
                'license_verified_by',
                'license_verified_at',
                'license_rejection_reason',
                'license_admin_notes',
            ]);
        });
    }
};
