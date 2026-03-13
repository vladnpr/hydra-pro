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
            $table->boolean('stream_status')->default(true)->after('result');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recon_flights', function (Blueprint $table) {
            $table->dropColumn('stream_status');
        });
    }
};
