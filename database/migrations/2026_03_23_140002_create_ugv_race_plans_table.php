<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ugv_race_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('combat_shift_id')->constrained('combat_shifts')->onDelete('cascade');
            $table->string('position_name');
            $table->string('coordinates')->nullable();
            $table->integer('order')->default(0);
            $table->string('status')->default('planned'); // planned, completed, skipped
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ugv_race_plans');
    }
};
