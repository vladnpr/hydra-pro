<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('air_defence_flights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('position_id')->constrained('positions');
            $table->foreignId('air_defence_drone_id')->constrained('air_defence_drones');
            $table->foreignId('air_defence_ammunition_id')->constrained('air_defence_ammunition');
            $table->string('coordinates')->nullable();
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->string('stream')->nullable();
            $table->string('result')->nullable();
            $table->boolean('detonation')->default(false);
            $table->text('comment')->nullable();
            $table->string('video_path')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('air_defence_flights');
    }
};
