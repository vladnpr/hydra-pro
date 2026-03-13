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
        Schema::table('recon_flights', function (Blueprint $table) {
            $table->dropForeign(['recon_ammunition_id']);
            $table->foreign('recon_ammunition_id')
                ->references('id')->on('ammunition')
                ->onDelete('restrict');
        });

        Schema::table('recon_flight_ammunition', function (Blueprint $table) {
            $table->dropForeign(['ammunition_id']);
            $table->foreign('ammunition_id')
                ->references('id')->on('ammunition')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recon_flight_ammunition', function (Blueprint $table) {
            $table->dropForeign(['ammunition_id']);
            $table->foreign('ammunition_id')
                ->references('id')->on('ammunition')
                ->onDelete('cascade');
        });

        Schema::table('recon_flights', function (Blueprint $table) {
            $table->dropForeign(['recon_ammunition_id']);
            $table->foreign('recon_ammunition_id')
                ->references('id')->on('ammunition')
                ->onDelete('set null');
        });
    }
};
