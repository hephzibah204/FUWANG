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
        Schema::create('parcel_custody_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parcel_id')->constrained('parcels')->onDelete('cascade');
            $table->foreignId('agent_id')->constrained('parcel_agents')->onDelete('cascade')->nullable();
            $table->string('event_type')->index(); // customer_to_agent, agent_to_driver, driver_to_agent, agent_to_customer
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parcel_custody_events');
    }
};
