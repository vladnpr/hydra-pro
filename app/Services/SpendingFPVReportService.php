<?php

namespace App\Services;

use App\DTOs\SpendFPVDTO;
use App\Models\CombatShift;
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

    public function getSpendingFPVReport(int $shiftId): ?SpendFPVDTO
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        $shift = $this->shiftRepository->find($shiftId);

        return $this->getSpendData($shift, $today, $tomorrow);
    }

    public function getReportByUserId(int $userId): ?SpendFPVDTO
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        if (!$shift = $this->shiftRepository->findActiveByUserId($userId)) {
            return null;
        }

        return $this->getSpendData($shift, $today, $tomorrow);
;
    }

    private function getSpendData(CombatShift $shift, Carbon $dateFrom, Carbon $dateTo)
    {
        $spendData = $this->flightRepository
            ->getSpendingByFlightsDate($shift->id, $dateFrom, $dateTo);

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
            $shift->id,
            $shift->position->name,
            $droneStats,
            $ammunitionStats
        );
    }
}
