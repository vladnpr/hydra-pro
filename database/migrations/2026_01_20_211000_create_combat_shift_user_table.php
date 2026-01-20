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
        Schema::create('combat_shift_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('combat_shift_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // Optionally migrate existing user_id from combat_shifts to this table
        // For now we will keep the user_id in combat_shifts to avoid breaking things immediately,
        // but we should eventually remove it or use it as "creator_id".
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combat_shift_user');
    }
};
