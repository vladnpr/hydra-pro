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
        Schema::table('positions', function (Blueprint $table) {
            $table->index('type');
        });

        Schema::table('combat_shifts', function (Blueprint $table) {
            $table->index('status');
            $table->index('started_at');
            $table->index(['position_id', 'status']);
        });

        Schema::table('combat_shift_flights', function (Blueprint $table) {
            $table->index('result');
            $table->index('detonation');
            $table->index(['combat_shift_id', 'result']);
        });

        Schema::table('recon_flights', function (Blueprint $table) {
            $table->index('result');
            $table->index(['combat_shift_id', 'result']);
        });

        Schema::table('vampire_flights', function (Blueprint $table) {
            $table->index('result');
            $table->index(['combat_shift_id', 'result']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vampire_flights', function (Blueprint $table) {
            $table->dropIndex(['vampire_flights_result_index']);
            $table->dropIndex(['vampire_flights_combat_shift_id_result_index']);
        });

        Schema::table('recon_flights', function (Blueprint $table) {
            $table->dropIndex(['recon_flights_result_index']);
            $table->dropIndex(['recon_flights_combat_shift_id_result_index']);
        });

        Schema::table('combat_shift_flights', function (Blueprint $table) {
            $table->dropIndex(['combat_shift_flights_result_index']);
            $table->dropIndex(['combat_shift_flights_detonation_index']);
            $table->dropIndex(['combat_shift_flights_combat_shift_id_result_index']);
        });

        Schema::table('combat_shifts', function (Blueprint $table) {
            $table->dropIndex(['combat_shifts_status_index']);
            $table->dropIndex(['combat_shifts_started_at_index']);
            $table->dropIndex(['combat_shifts_position_id_status_index']);
        });

        Schema::table('positions', function (Blueprint $table) {
            $table->dropIndex(['positions_type_index']);
        });
    }
};
