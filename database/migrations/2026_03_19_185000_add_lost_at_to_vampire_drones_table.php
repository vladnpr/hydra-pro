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
        Schema::table('vampire_drones', function (Blueprint $table) {
            $table->timestamp('lost_at')->nullable()->after('position_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vampire_drones', function (Blueprint $table) {
            $table->dropColumn('lost_at');
        });
    }
};
