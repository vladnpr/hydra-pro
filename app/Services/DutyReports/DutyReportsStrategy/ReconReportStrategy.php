<?php

namespace App\Services\DutyReports\DutyReportsStrategy;

use App\DTOs\DutyReportCombatShiftDTO;
use App\DTOs\ReconDutyReportDTO;
use App\Repositories\AmmunitionRepository;
use App\Repositories\ReconShiftDataRepository;
use Carbon\Carbon;

class ReconReportStrategy implements DutyReportStrategy
{
    public function __construct(
        private readonly ReconShiftDataRepository $reconShiftDataRepository,
        private readonly AmmunitionRepository $ammunitionRepository,
    )
    {
    }

    public function getReport(DutyReportCombatShiftDTO $shift, Carbon $from, Carbon $to): ReconDutyReportDTO
    {
        $dronesRemainingData = $this->reconShiftDataRepository->getRemainingDrones($shift->getCombatShiftID());
        $flightsData = $this->reconShiftDataRepository->getFlights($from, $to, $shift->getCombatShiftID());
        $ammunitionRemainingData = $this->ammunitionRepository->getAmmunitionRemaining($shift->getCombatShiftID());

        return new ReconDutyReportDTO(
            $shift,
            $dronesRemainingData,
            $flightsData,
            $ammunitionRemainingData
        );
    }
}
