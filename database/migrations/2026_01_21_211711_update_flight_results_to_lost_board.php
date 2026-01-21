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
        \DB::table('combat_shift_flights')
            ->where('result', 'недольот')
            ->update(['result' => 'втрата борту']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::table('combat_shift_flights')
            ->where('result', 'втрата борту')
            ->update(['result' => 'недольот']);
    }
};
