<?php

namespace App\Services;

use App\DTOs\SpendFPVDTO;
use App\Repositories\CombatShiftFlightsRepository;
use Carbon\Carbon;

readonly class SpendingFPVReportService
{
    public function __construct(
        private CombatShiftFlightsRepository $flightRepository
    )
    {
    }

    public function getSpendingFPVReport(int $shiftId): SpendFPVDTO
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

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

        return new SpendFPVDTO($droneStats, $ammunitionStats);
    }
}
