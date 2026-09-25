<?php

namespace App\Services\DutyReports\DutyReportsStrategy;

use App\DTOs\ADDutyReportDTO;
use App\DTOs\DutyReportCombatShiftDTO;
use App\Repositories\AirDefenceShiftDataRepository;
use Carbon\Carbon;

class AIrDefenceReportStrategy implements DutyReportStrategy
{
    public function __construct(
        private readonly AirDefenceShiftDataRepository $airDefenceShiftDataRepository
    )
    {

    }

    public function getReport(DutyReportCombatShiftDTO $shift, Carbon $from, Carbon $to): ADDutyReportDTO
    {
        $dronesRemaining = $this->airDefenceShiftDataRepository->getDronesRemaining($shift->getCombatShiftID());
        $flights = '';
        $ammunition = '';

        return new ADDutyReportDTO(
            $dronesRemaining,
            $flights,
            $ammunition
        );
    }
}
