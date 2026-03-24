<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ugv_races', function (Blueprint $table) {
            $table->json('checkpoints')->nullable()->after('ugv_race_plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('ugv_races', function (Blueprint $table) {
            $table->dropColumn('checkpoints');
        });
    }
};
