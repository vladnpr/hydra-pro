<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ugv_race_ammunition', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ugv_race_id')->constrained('ugv_races')->onDelete('cascade');
            $table->foreignId('ammunition_id')->constrained('ammunition')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ugv_race_ammunition');
    }
};
