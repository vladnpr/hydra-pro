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
        Schema::table('combat_shifts', function (Blueprint $table) {
            $table->json('damaged_drones')->nullable()->after('ended_at');
            $table->json('damaged_coils')->nullable()->after('damaged_drones');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('combat_shifts', function (Blueprint $table) {
            $table->dropColumn(['damaged_drones', 'damaged_coils']);
        });
    }
};
