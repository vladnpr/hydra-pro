<?php

namespace App\Services\DutyReports\DutyReportsStrategy;

use App\DTOs\DutyReportCombatShiftDTO;
use App\DTOs\FPVDutyReportDTO;
use App\Repositories\FPVShiftDataRepository;
use Carbon\Carbon;

final class FPVReportStrategy implements DutyReportStrategy
{
    public function __construct(
        private readonly FPVShiftDataRepository $FPVShiftDataRepository,
    )
    {
    }

    public function getReport(DutyReportCombatShiftDTO $shift, Carbon $from, Carbon $to): FPVDutyReportDTO
    {
        $dronesRemaining = $this->FPVShiftDataRepository->getFPVDronesRemaining($shift->getCombatShiftID());
        $flights = $this->FPVShiftDataRepository->getFPVFlights($from, $to, $shift->getCombatShiftID());;

        return new FPVDutyReportDTO(
            $shift,
            $dronesRemaining,
            $flights
        );
    }
}
