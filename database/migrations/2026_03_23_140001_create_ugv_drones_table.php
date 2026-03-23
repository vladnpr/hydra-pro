<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ugv_drones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('serial_number')
                ->unique()
                ->nullable();
            $table->string('shift_type')
                ->default(\App\Enums\ShiftTypeEnum::DAY->value);
            $table->string('status')->default('active');
            $table->foreignId('position_id')
                ->constrained('positions');
            $table->dateTime('lost_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ugv_drones');
    }
};
