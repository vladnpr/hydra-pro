<?php

namespace App\Services;

use App\DTOs\SpendFPVDTO;
use App\Repositories\CombatShiftFlightsRepository;
use App\Repositories\Eloquent\EloquentCombatShiftRepository;
use Carbon\Carbon;

readonly class SpendingFPVReportService
{
    public function __construct(
        private CombatShiftFlightsRepository $flightRepository,
        private EloquentCombatShiftRepository $shiftRepository,
    )
    {
    }

    public function getSpendingFPVReport(int $shiftId): SpendFPVDTO
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        $shift = $this->shiftRepository->find($shiftId);

        $spendData = $this->flightRepository->getSpendingByFlightsDate($shiftId, $today, $tomorrow);

        $ammunitionStats = $spendData
            ->pluck('ammunition_name')
            ->countBy()
            ->map(fn($count, $name) => [
                'name' => $name,
                'count' => $count,
            ])
            ->values();

        $droneStats = $spendData
            ->pluck('drone_name')
            ->countBy()
            ->map(fn($count, $name) => [
                'name' => $name,
                'count' => $count,
            ])
            ->values();

        return new SpendFPVDTO(
            $shiftId,
            $shift->position->name,
            $droneStats,
            $ammunitionStats);
    }
}
