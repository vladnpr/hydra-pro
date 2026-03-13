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
        Schema::table('combat_shift_crew', function (Blueprint $table) {
            $table->string('shift_type')
                ->default(\App\Enums\ShiftTypeEnum::DAY->value)
                ->nullable(); // day or night
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('combat_shift_crew', function (Blueprint $table) {
            $table->dropColumn('shift_type');
        });
    }
};
