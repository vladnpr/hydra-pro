<?php

namespace App\Services\DutyReports\DutyReportsStrategy;

use App\DTOs\DutyReportCombatShiftDTO;
use App\DTOs\VampireDutyReportDTO;
use App\Repositories\VampireShiftDataRepository;
use Carbon\Carbon;

class VampireReportStrategy implements DutyReportStrategy
{
    public function __construct(
        private readonly VampireShiftDataRepository $vampireShiftDataRepository
    )
    {
    }

    public function getReport(DutyReportCombatShiftDTO $shift, Carbon $from, Carbon $to): VampireDutyReportDTO
    {
        $dronesRemaining = $this->vampireShiftDataRepository->getDronesRemaining($shift->getPositionID());
        $flights = '';
        $ammoRemaining = '';

        return new VampireDutyReportDTO(
            $shift,
            $dronesRemaining,
            $flights,
            $ammoRemaining,
        );
    }
}
