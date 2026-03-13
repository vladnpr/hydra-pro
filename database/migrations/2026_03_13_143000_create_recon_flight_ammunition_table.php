<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recon_flight_ammunition', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recon_flight_id')->constrained('recon_flights')->cascadeOnDelete();
            $table->foreignId('ammunition_id')->constrained('ammunition')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });

        // Migrate existing data if needed, but since it's a new feature, maybe just remove the column later.
        // For now let's just keep the column but we will use the pivot table.
    }

    public function down(): void
    {
        Schema::dropIfExists('recon_flight_ammunition');
    }
};
