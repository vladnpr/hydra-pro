<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vampire_flights', function (Blueprint $table) {
            $table->foreignId('ammunition_id')->nullable()->constrained('ammunition')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('vampire_flights', function (Blueprint $table) {
            $table->dropForeign(['ammunition_id']);
            $table->dropColumn('ammunition_id');
        });
    }
};
