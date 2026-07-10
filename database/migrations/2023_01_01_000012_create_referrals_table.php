<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('referrals')) {
            return;
        }

        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referred_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('referral_code')->index();
            $table->string('status')->default('registered')->index();
            $table->timestamp('registered_at')->nullable()->index();
            $table->timestamp('first_funded_at')->nullable()->index();
            $table->decimal('reward_amount', 15, 2)->default(0);
            $table->string('reward_status')->default('none')->index();
            $table->string('reward_transaction_id')->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique('referred_user_id');
            $table->index(['referrer_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};

