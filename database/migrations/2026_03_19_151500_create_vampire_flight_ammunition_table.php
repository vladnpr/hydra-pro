<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create table for Many-to-Many relation with quantity
        Schema::create('vampire_flight_ammunition', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vampire_flight_id')->constrained('vampire_flights')->onDelete('cascade');
            $table->foreignId('ammunition_id')->constrained('ammunition')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });

        // 2. Remove previous singular ammunition_id column
        Schema::table('vampire_flights', function (Blueprint $table) {
            $table->dropForeign(['ammunition_id']);
            $table->dropColumn('ammunition_id');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('vampire_flight_ammunition');
        Schema::enableForeignKeyConstraints();

        Schema::table('vampire_flights', function (Blueprint $table) {
            $table->foreignId('ammunition_id')->nullable()->constrained('ammunition')->onDelete('set null');
        });
    }
};
