<?php

namespace App\Services\DutyReports\DutyReportsStrategy;

use App\DTOs\DutyReportCombatShiftDTO;
use App\DTOs\VampireDutyReportDTO;
use App\Repositories\AmmunitionRepository;
use App\Repositories\VampireShiftDataRepository;
use Carbon\Carbon;

class VampireReportStrategy implements DutyReportStrategy
{
    public function __construct(
        private readonly VampireShiftDataRepository $vampireShiftDataRepository,
        private readonly AmmunitionRepository $ammunitionRepository,
    )
    {
    }

    public function getReport(DutyReportCombatShiftDTO $shift, Carbon $from, Carbon $to): VampireDutyReportDTO
    {
        $dronesRemaining = $this->vampireShiftDataRepository->getDronesRemaining($shift->getPositionID());
        $flights = $this->vampireShiftDataRepository->getFlights($from, $to, $shift->getCombatShiftID());
        $ammoRemaining = $this->ammunitionRepository->getAmmunitionRemaining($shift->getCombatShiftID());

        return new VampireDutyReportDTO(
            $shift,
            $dronesRemaining,
            $flights,
            $ammoRemaining,
        );
    }
}
