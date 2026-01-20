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
        Schema::create('combat_shift_ammunition', function (Blueprint $table) {
            $table->id();
            $table->foreignId('combat_shift_id')->constrained('combat_shifts')->onDelete('cascade');
            $table->foreignId('ammunition_id')->constrained('ammunition')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combat_shift_ammunition');
    }
};
