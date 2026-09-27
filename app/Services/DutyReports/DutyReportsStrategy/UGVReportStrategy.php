<?php

namespace App\Services\DutyReports\DutyReportsStrategy;

use App\DTOs\DutyReportCombatShiftDTO;
use App\DTOs\UGVDutyReportDTO;
use App\Repositories\AmmunitionRepository;
use App\Repositories\UGVShiftDataRepository;
use Carbon\Carbon;

class UGVReportStrategy implements DutyReportStrategy
{
    public function __construct(
        private UGVShiftDataRepository $shiftDataRepository,
        private AmmunitionRepository $ammunitionRepository
    )
    {
    }

    public function getReport(DutyReportCombatShiftDTO $shift, Carbon $from, Carbon $to): UGVDutyReportDTO
    {
        $UGVDronesRemaining = $this->shiftDataRepository->getUGVRemaining($shift->getPositionID());
        $races = $this->shiftDataRepository->getRaces($from, $to, $shift->getCombatShiftID());
        $ammoRemaining = $this->ammunitionRepository->getAmmunitionRemaining($shift->getCombatShiftID());

        return new UGVDutyReportDTO(
            $shift,
            $UGVDronesRemaining,
            $races,
            $ammoRemaining
        );
    }
}
