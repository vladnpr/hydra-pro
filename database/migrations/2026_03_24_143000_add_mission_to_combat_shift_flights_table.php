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
        Schema::table('combat_shift_flights', function (Blueprint $table) {
            $table->string('mission')->default('strike')->after('ammunition_id'); // strike, patrol, logistics
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('combat_shift_flights', function (Blueprint $table) {
            $table->dropColumn('mission');
        });
    }
};
