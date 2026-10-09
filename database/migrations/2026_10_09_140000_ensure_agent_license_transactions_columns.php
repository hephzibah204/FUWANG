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
        if (!Schema::hasTable('agent_license_transactions')) {
            Schema::create('agent_license_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('agent_id')->constrained('enrollment_agents')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->decimal('amount', 12, 2);
                $table->string('payment_method', 50);
                $table->string('reference', 120)->unique();
                $table->string('gateway_reference', 120)->nullable()->index();
                $table->string('proof_path')->nullable();
                $table->enum('status', ['pending', 'completed', 'failed', 'rejected'])->default('pending')->index();
                $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
                $table->json('meta')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
            return;
        }

        Schema::table('agent_license_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('agent_license_transactions', 'reference')) {
                // If payment_reference or transaction_reference existed, rename or add reference
                if (Schema::hasColumn('agent_license_transactions', 'payment_reference')) {
                    $table->renameColumn('payment_reference', 'reference');
                } elseif (Schema::hasColumn('agent_license_transactions', 'transaction_reference')) {
                    $table->renameColumn('transaction_reference', 'reference');
                } else {
                    $table->string('reference', 120)->nullable()->after('payment_method');
                }
            }

            if (!Schema::hasColumn('agent_license_transactions', 'gateway_reference')) {
                $table->string('gateway_reference', 120)->nullable()->after('reference');
            }

            if (!Schema::hasColumn('agent_license_transactions', 'proof_path')) {
                $table->string('proof_path')->nullable()->after('gateway_reference');
            }

            if (!Schema::hasColumn('agent_license_transactions', 'status')) {
                $table->string('status', 30)->default('pending')->after('proof_path');
            }

            if (!Schema::hasColumn('agent_license_transactions', 'admin_id')) {
                $table->foreignId('admin_id')->nullable()->after('status');
            }

            if (!Schema::hasColumn('agent_license_transactions', 'meta')) {
                $table->json('meta')->nullable()->after('admin_id');
            }

            if (!Schema::hasColumn('agent_license_transactions', 'notes')) {
                $table->text('notes')->nullable()->after('meta');
            }

            if (!Schema::hasColumn('agent_license_transactions', 'payment_method')) {
                $table->string('payment_method', 50)->nullable()->after('amount');
            }

            if (!Schema::hasColumn('agent_license_transactions', 'amount')) {
                $table->decimal('amount', 12, 2)->default(0)->after('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructive rollback to avoid losing license transaction history
    }
};
