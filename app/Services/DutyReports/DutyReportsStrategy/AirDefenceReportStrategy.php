<?php

namespace App\Services\DutyReports\DutyReportsStrategy;

use App\DTOs\ADDutyReportDTO;
use App\DTOs\DutyReportCombatShiftDTO;
use App\Repositories\ADShiftDataRepository;
use Carbon\Carbon;

class AirDefenceReportStrategy implements DutyReportStrategy
{
    public function __construct(
        private readonly ADShiftDataRepository $airDefenceShiftDataRepository
    )
    {

    }

    public function getReport(DutyReportCombatShiftDTO $shift, Carbon $from, Carbon $to): ADDutyReportDTO
    {
        $dronesRemaining = $this->airDefenceShiftDataRepository->getDronesRemaining($shift->getCombatShiftID());
        $flights = $this->airDefenceShiftDataRepository->getFlights($from, $to);
        $ammunition = '';

        return new ADDutyReportDTO(
            $dronesRemaining,
            $flights,
            $ammunition
        );
    }
}
