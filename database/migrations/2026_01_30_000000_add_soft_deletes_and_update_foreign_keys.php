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
        Schema::table('drones', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('ammunition', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Видаляємо каскадне видалення з польотів, щоб при випадковому фізичному видаленні (якщо воно буде) дані не зникали
        // Хоча основна ідея — використовувати SoftDeletes
        Schema::table('combat_shift_flights', function (Blueprint $table) {
            $table->dropForeign(['drone_id']);
            $table->dropForeign(['ammunition_id']);

            $table->foreign('drone_id')->references('id')->on('drones')->onDelete('restrict');
            $table->foreign('ammunition_id')->references('id')->on('ammunition')->onDelete('restrict');
        });

        // Також для півот-таблиць
        Schema::table('combat_shift_drone', function (Blueprint $table) {
            $table->dropForeign(['drone_id']);
            $table->foreign('drone_id')->references('id')->on('drones')->onDelete('restrict');
        });

        Schema::table('combat_shift_ammunition', function (Blueprint $table) {
            $table->dropForeign(['ammunition_id']);
            $table->foreign('ammunition_id')->references('id')->on('ammunition')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('combat_shift_ammunition', function (Blueprint $table) {
            $table->dropForeign(['ammunition_id']);
            $table->foreign('ammunition_id')->references('id')->on('ammunition')->onDelete('cascade');
        });

        Schema::table('combat_shift_drone', function (Blueprint $table) {
            $table->dropForeign(['drone_id']);
            $table->foreign('drone_id')->references('id')->on('drones')->onDelete('cascade');
        });

        Schema::table('combat_shift_flights', function (Blueprint $table) {
            $table->dropForeign(['drone_id']);
            $table->dropForeign(['ammunition_id']);

            $table->foreign('drone_id')->references('id')->on('drones')->onDelete('cascade');
            $table->foreign('ammunition_id')->references('id')->on('ammunition')->onDelete('cascade');
        });

        Schema::table('ammunition', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('drones', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
