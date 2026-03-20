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
            $table->timestamp('landing_time')->nullable()->after('flight_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recon_flights', function (Blueprint $table) {
            $table->dropColumn('landing_time');
        });
    }
};
