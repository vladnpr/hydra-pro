<?php

namespace App\Services;

use App\Enums\PositionTypesEnum;
use App\Repositories\Contracts\PositionRepositoryInterface;
use App\Repositories\DutyReportRepository;
use Carbon\Carbon;

class DutyReportService
{
    public function __construct(
        private readonly PositionRepositoryInterface $positionRepository,
        private readonly DutyReportRepository $dutyReportRepository
    )
    {
    }

    public function dutyReport(?Carbon $from = null, ?Carbon $to = null): array
    {
        $report = [];
        $activePositons = $this->positionRepository->getActive();

        foreach ($activePositons as $activePositon) {
            if ($activePositon->type === PositionTypesEnum::FPV->value) {
                $inventoryData = $this->dutyReportRepository->fpvInventoryData($activePositon->id);
                $flightsData = $this->dutyReportRepository->fpvFlightsStatsData(
                    $activePositon->id,
                    $from ?? Carbon::now()->subDays(7)->toDateTimeString(),
                    $to ?? Carbon::now()->toDateTimeString()
                );

                $report[$activePositon->name] = [
                    'drones' => $inventoryData,
                    'flights' => $flightsData
                ];
            }
        }

        return $report;
    }
}
