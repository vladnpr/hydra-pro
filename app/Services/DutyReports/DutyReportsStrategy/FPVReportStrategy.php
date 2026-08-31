<?php

namespace App\Services\DutyReports\DutyReportsStrategy;

use App\Collections\FPVDronesRemainingDTOCollection;
use App\DTOs\DutyReportCombatShiftDTO;
use App\DTOs\FPVDutyReportDTO;
use App\Repositories\DutyReportsRepository;

class FPVReportStrategy implements DutyReportStrategy
{
    public function __construct(
        private readonly DutyReportsRepository $dutyReportsRepository
    )
    {
    }

    public function getReport(DutyReportCombatShiftDTO $shift): FPVDutyReportDTO
    {
        $dronesRemaining = $this->getDronesRemaining($shift->getCombatShiftID());

        $data = new FPVDutyReportDTO(
            $shift,
            $dronesRemaining,
        );
    }

    private function getDronesRemaining(int $combatShiftId): FPVDronesRemainingDTOCollection
    {
        return $this->dutyReportsRepository->getDronesRemaining($combatShiftId);
    }
}
