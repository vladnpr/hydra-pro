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
        Schema::table('vampire_flights', function (Blueprint $table) {
            $table->string('coordinates')->nullable()->after('vampire_flight_plan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vampire_flights', function (Blueprint $table) {
            $table->dropColumn('coordinates');
        });
    }
};
