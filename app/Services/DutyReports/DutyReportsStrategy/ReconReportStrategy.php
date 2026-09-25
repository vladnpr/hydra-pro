<?php

namespace App\Services\DutyReports\DutyReportsStrategy;

use App\DTOs\DutyReportCombatShiftDTO;
use App\DTOs\ReconDutyReportDTO;
use App\Repositories\AmmunitionRepository;
use App\Repositories\ReconShiftDataRepository;
use Carbon\Carbon;

readonly class ReconReportStrategy implements DutyReportStrategy
{
    public function __construct(
        private ReconShiftDataRepository $reconShiftDataRepository,
        private AmmunitionRepository     $ammunitionRepository,
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
