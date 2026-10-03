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
        Schema::create('parcel_couriers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('adapter_class');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert Default FuwaPost Courier for the MVP
        \Illuminate\Support\Facades\DB::table('parcel_couriers')->insert([
            'name' => 'FuwaPost',
            'adapter_class' => \App\Services\Parcels\Adapters\FuwaPostAdapter::class,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parcel_couriers');
    }
};
