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
            if (!Schema::hasColumn('enrollment_agents', 'agent_type')) {
                $table->enum('agent_type', ['existing', 'new'])->default('new')->after('user_id');
            }
            if (!Schema::hasColumn('enrollment_agents', 'company_agent_code')) {
                $table->string('company_agent_code')->nullable()->after('agent_type');
            }
            if (!Schema::hasColumn('enrollment_agents', 'state')) {
                $table->string('state')->nullable()->after('phone_number');
            }
            if (!Schema::hasColumn('enrollment_agents', 'has_machine')) {
                $table->boolean('has_machine')->default(false)->after('state');
            }
            if (!Schema::hasColumn('enrollment_agents', 'utility_bill_path')) {
                $table->string('utility_bill_path')->nullable()->after('machine_imei');
            }
            if (!Schema::hasColumn('enrollment_agents', 'picture_path')) {
                $table->string('picture_path')->nullable()->after('utility_bill_path');
            }
            if (!Schema::hasColumn('enrollment_agents', 'business_registration_number')) {
                $table->string('business_registration_number')->nullable()->after('picture_path');
            }
            if (!Schema::hasColumn('enrollment_agents', 'business_registration_doc_path')) {
                $table->string('business_registration_doc_path')->nullable()->after('business_registration_number');
            }
            if (!Schema::hasColumn('enrollment_agents', 'nin_server_status')) {
                $table->enum('nin_server_status', ['verified', 'pending_fallback', 'failed'])->default('pending_fallback')->after('nin_verified');
            }
            if (!Schema::hasColumn('enrollment_agents', 'accepted_terms')) {
                $table->boolean('accepted_terms')->default(false)->after('status');
            }
            if (!Schema::hasColumn('enrollment_agents', 'terms_accepted_at')) {
                $table->timestamp('terms_accepted_at')->nullable()->after('accepted_terms');
            }
            if (!Schema::hasColumn('enrollment_agents', 'onboarding_step')) {
                $table->enum('onboarding_step', ['basic_info', 'kyc_docs', 'compliance_terms', 'submitted'])->default('basic_info')->after('terms_accepted_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollment_agents', function (Blueprint $table) {
            $table->dropColumn([
                'agent_type',
                'company_agent_code',
                'state',
                'has_machine',
                'utility_bill_path',
                'picture_path',
                'business_registration_number',
                'business_registration_doc_path',
                'nin_server_status',
                'accepted_terms',
                'terms_accepted_at',
                'onboarding_step',
            ]);
        });
    }
};
