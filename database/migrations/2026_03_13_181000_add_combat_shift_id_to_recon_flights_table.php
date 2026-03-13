<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recon_flights', function (Blueprint $table) {
            $table->foreignId('combat_shift_id')->nullable()->after('id')->constrained('combat_shifts')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('recon_flights', function (Blueprint $table) {
            $table->dropConstrainedForeignId('combat_shift_id');
        });
    }
};
