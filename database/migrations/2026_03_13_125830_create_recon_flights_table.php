<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ReconMissionTypesEnum;
use \App\Enums\ReconMissionResultsEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recon_flights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recon_drone_id')
                ->constrained('recon_drones');
            $table->foreignId('recon_ammunition_id')
                ->nullable()
                ->constrained('recon_ammunition')
                ->nullOnDelete();
            $table->string('mission_type')
                ->default(
                    ReconMissionTypesEnum::RECON->value
                ); // розвідка, бойова (скид)
            $table->string('coordinates');
            $table->timestamp('flight_time');
            $table->string('result')
                ->default(ReconMissionResultsEnum::SUCCESS->value); // втрата борту, відпрацювали, інше
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recon_flights');
    }
};
