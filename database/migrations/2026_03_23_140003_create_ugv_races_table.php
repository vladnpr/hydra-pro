<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ugv_races', function (Blueprint $table) {
            $table->id();
            $table->foreignId('combat_shift_id')->constrained('combat_shifts')->onDelete('cascade');
            $table->foreignId('ugv_race_plan_id', 'ugv_ra_plan_id_foreign')->nullable()->constrained('ugv_race_plans')->onDelete('set null');
            $table->foreignId('ugv_drone_id')->constrained('ugv_drones');
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->boolean('stream_status')->default(false);
            $table->string('mission_type'); // logistics, combat, evac
            $table->string('result'); // loss, worked
            $table->text('comment')->nullable();
            $table->string('coordinates')->nullable();
            $table->string('shift_type')->default(\App\Enums\ShiftTypeEnum::DAY->value);
            $table->string('video_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('ugv_races');
        Schema::enableForeignKeyConstraints();
    }
};
