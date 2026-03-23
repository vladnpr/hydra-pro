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
        Schema::create('combat_shift_air_defence_drone', function (Blueprint $table) {
            $table->id();
            $table->foreignId('combat_shift_id')->constrained('combat_shifts')->onDelete('cascade');
            $table->foreignId('air_defence_drone_id')->constrained('air_defence_drones', 'id', 'cs_ad_drone_foreign')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combat_shift_air_defence_drone');
    }
};
