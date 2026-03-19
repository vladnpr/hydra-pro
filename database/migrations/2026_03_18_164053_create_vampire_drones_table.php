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
        Schema::create('vampire_drones', function (Blueprint $table) {
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
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('vampire_drones');
        Schema::enableForeignKeyConstraints();
    }
};
