<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vampire_flights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('combat_shift_id')->constrained('combat_shifts')->onDelete('cascade');
            $table->foreignId('vampire_flight_plan_id')->nullable()->constrained('vampire_flight_plans')->onDelete('set null');
            $table->foreignId('vampire_drone_id')->constrained('vampire_drones');
            $table->dateTime('flight_time');
            $table->boolean('stream_status')->default(false);
            $table->string('mission_type'); // logistics, combat
            $table->string('result'); // loss, worked
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('vampire_flights');
        Schema::enableForeignKeyConstraints();
    }
};
