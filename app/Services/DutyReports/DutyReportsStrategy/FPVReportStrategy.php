<?php

namespace App\Services\DutyReports\DutyReportsStrategy;

use App\Collections\FPVDronesRemainingDTOCollection;
use App\Collections\FPVFlightDTOCollection;
use App\DTOs\DutyReportCombatShiftDTO;
use App\DTOs\FPVDutyReportDTO;
use App\Repositories\CombatShiftsRepository;
use Carbon\Carbon;

class FPVReportStrategy implements DutyReportStrategy
{
    public function __construct(
        private readonly CombatShiftsRepository $dutyReportsRepository
    )
    {
    }

    public function getReport(DutyReportCombatShiftDTO $shift, Carbon $from, Carbon $to): FPVDutyReportDTO
    {
        $dronesRemaining = $this->getDronesRemaining($shift->getCombatShiftID());
        $flights = $this->getFlights($shift->getCombatShiftID(), $from, $to);

        return new FPVDutyReportDTO(
            $shift,
            $dronesRemaining,
            $flights
        );
    }

    private function getDronesRemaining(int $combatShiftId): FPVDronesRemainingDTOCollection
    {
        return $this->dutyReportsRepository->getFPVDronesRemaining($combatShiftId);
    }

    private function getFlights(int $combatShiftId, Carbon $from, Carbon $to): FPVFlightDTOCollection
    {
        return $this->dutyReportsRepository->getFPVFlights($from, $to, $combatShiftId);
    }
}
