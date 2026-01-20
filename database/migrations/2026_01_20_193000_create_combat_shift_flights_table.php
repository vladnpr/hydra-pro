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
        Schema::create('combat_shift_flights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('combat_shift_id')->constrained('combat_shifts')->onDelete('cascade');
            $table->foreignId('drone_id')->constrained('drones')->onDelete('cascade');
            $table->foreignId('ammunition_id')->constrained('ammunition')->onDelete('cascade');
            $table->string('coordinates');
            $table->timestamp('flight_time');
            $table->string('result'); // влучання, удар в районі цілі, недольот
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combat_shift_flights');
    }
};
