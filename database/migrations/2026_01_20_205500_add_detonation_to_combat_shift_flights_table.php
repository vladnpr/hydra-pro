<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('combat_shift_flights', function (Blueprint $table) {
            $table->string('detonation')->default('ні'); // так, ні, інше
        });
    }

    public function down(): void
    {
        Schema::table('combat_shift_flights', function (Blueprint $table) {
            $table->dropColumn('detonation');
        });
    }
};
