<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vampire_flights', function (Blueprint $table) {
            $table->string('shift_type')->default(\App\Enums\ShiftTypeEnum::NIGHT->value)->after('result');
        });
    }

    public function down(): void
    {
        Schema::table('vampire_flights', function (Blueprint $table) {
            $table->dropColumn('shift_type');
        });
    }
};
