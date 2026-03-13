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
        Schema::create('recon_drones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('serial_number')
                ->unique()
                ->nullable();
            $table->string('status')->default('active');
            $table->foreignId('position_id')
                ->constrained('positions');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recon_drones');
    }
};
