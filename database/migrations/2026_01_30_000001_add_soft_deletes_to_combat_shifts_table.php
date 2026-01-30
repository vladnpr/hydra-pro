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
            $table->softDeletes();
        });

        // Оновлюємо зовнішні ключі на RESTRICT
        Schema::table('combat_shift_flights', function (Blueprint $table) {
            $table->dropForeign(['combat_shift_id']);
            $table->foreign('combat_shift_id')->references('id')->on('combat_shifts')->onDelete('restrict');
        });

        Schema::table('combat_shift_drone', function (Blueprint $table) {
            $table->dropForeign(['combat_shift_id']);
            $table->foreign('combat_shift_id')->references('id')->on('combat_shifts')->onDelete('restrict');
        });

        Schema::table('combat_shift_ammunition', function (Blueprint $table) {
            $table->dropForeign(['combat_shift_id']);
            $table->foreign('combat_shift_id')->references('id')->on('combat_shifts')->onDelete('restrict');
        });

        Schema::table('combat_shift_user', function (Blueprint $table) {
            $table->dropForeign(['combat_shift_id']);
            $table->foreign('combat_shift_id')->references('id')->on('combat_shifts')->onDelete('restrict');
        });

        Schema::table('combat_shift_crew', function (Blueprint $table) {
            $table->dropForeign(['combat_shift_id']);
            $table->foreign('combat_shift_id')->references('id')->on('combat_shifts')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('combat_shift_crew', function (Blueprint $table) {
            $table->dropForeign(['combat_shift_id']);
            $table->foreign('combat_shift_id')->references('id')->on('combat_shifts')->onDelete('cascade');
        });

        Schema::table('combat_shift_user', function (Blueprint $table) {
            $table->dropForeign(['combat_shift_id']);
            $table->foreign('combat_shift_id')->references('id')->on('combat_shifts')->onDelete('cascade');
        });

        Schema::table('combat_shift_ammunition', function (Blueprint $table) {
            $table->dropForeign(['combat_shift_id']);
            $table->foreign('combat_shift_id')->references('id')->on('combat_shifts')->onDelete('cascade');
        });

        Schema::table('combat_shift_drone', function (Blueprint $table) {
            $table->dropForeign(['combat_shift_id']);
            $table->foreign('combat_shift_id')->references('id')->on('combat_shifts')->onDelete('cascade');
        });

        Schema::table('combat_shift_flights', function (Blueprint $table) {
            $table->dropForeign(['combat_shift_id']);
            $table->foreign('combat_shift_id')->references('id')->on('combat_shifts')->onDelete('cascade');
        });

        Schema::table('combat_shifts', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
