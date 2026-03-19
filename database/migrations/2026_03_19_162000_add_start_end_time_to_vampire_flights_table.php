<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vampire_flights', function (Blueprint $table) {
            $table->dateTime('start_time')->nullable()->after('vampire_drone_id');
            $table->dateTime('end_time')->nullable()->after('start_time');
        });

        // Copy data from flight_time to start_time
        \Illuminate\Support\Facades\DB::table('vampire_flights')->update([
            'start_time' => \Illuminate\Support\Facades\DB::raw('flight_time'),
            'end_time' => \Illuminate\Support\Facades\DB::raw('flight_time'), // default to same as start_time for existing records
        ]);

        Schema::table('vampire_flights', function (Blueprint $table) {
            $table->dateTime('start_time')->nullable(false)->change();
            $table->dropColumn('flight_time');
        });
    }

    public function down(): void
    {
        Schema::table('vampire_flights', function (Blueprint $table) {
            $table->dateTime('flight_time')->nullable()->after('vampire_drone_id');
        });

        \Illuminate\Support\Facades\DB::table('vampire_flights')->update([
            'flight_time' => \Illuminate\Support\Facades\DB::raw('start_time'),
        ]);

        Schema::table('vampire_flights', function (Blueprint $table) {
            $table->dateTime('flight_time')->nullable(false)->change();
            $table->dropColumn(['start_time', 'end_time']);
        });
    }
};
