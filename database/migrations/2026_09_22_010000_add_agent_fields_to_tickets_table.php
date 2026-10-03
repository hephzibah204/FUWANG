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
        if (!Schema::hasTable('tickets')) {
            Schema::create('tickets', function (Blueprint $table) {
                $table->id();
                $table->string('user_email');
                $table->foreignId('agent_id')->nullable()->constrained('enrollment_agents')->nullOnDelete();
                $table->string('subject');
                $table->string('category')->default('other');
                $table->string('machine_imei')->nullable();
                $table->string('priority')->default('medium');
                $table->string('status')->default('open');
                $table->string('attachment_path')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('tickets', function (Blueprint $table) {
                if (!Schema::hasColumn('tickets', 'agent_id')) {
                    $table->foreignId('agent_id')->nullable()->after('user_email')->constrained('enrollment_agents')->nullOnDelete();
                }
                if (!Schema::hasColumn('tickets', 'category')) {
                    $table->string('category')->default('other')->after('agent_id');
                }
                if (!Schema::hasColumn('tickets', 'machine_imei')) {
                    $table->string('machine_imei')->nullable()->after('category');
                }
                if (!Schema::hasColumn('tickets', 'priority')) {
                    $table->string('priority')->default('medium')->after('machine_imei');
                }
                if (!Schema::hasColumn('tickets', 'attachment_path')) {
                    $table->string('attachment_path')->nullable()->after('status');
                }
            });
        }

        if (!Schema::hasTable('ticket_replies')) {
            Schema::create('ticket_replies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade');
                $table->enum('sender_type', ['user', 'admin'])->default('user');
                $table->text('message');
                $table->string('attachment_path')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('ticket_replies', function (Blueprint $table) {
                if (!Schema::hasColumn('ticket_replies', 'attachment_path')) {
                    $table->string('attachment_path')->nullable()->after('message');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_replies');
        Schema::dropIfExists('tickets');
    }
};
