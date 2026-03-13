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
            $table->dropForeign(['recon_drone_id']);
            $table->foreign('recon_drone_id')
                ->references('id')
                ->on('recon_drones')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recon_flights', function (Blueprint $table) {
            $table->dropForeign(['recon_drone_id']);
            $table->foreign('recon_drone_id')
                ->references('id')
                ->on('recon_drones');
        });
    }
};
