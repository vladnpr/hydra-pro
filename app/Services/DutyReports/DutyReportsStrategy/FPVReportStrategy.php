<?php

namespace App\Services\DutyReports\DutyReportsStrategy;

use App\DTOs\DutyReportCombatShiftDTO;
use App\DTOs\FPVDutyReportDTO;
use App\Repositories\AmmunitionRepository;
use App\Repositories\FPVShiftDataRepository;
use Carbon\Carbon;

final readonly class FPVReportStrategy implements DutyReportStrategy
{
    public function __construct(
        private FPVShiftDataRepository $FPVShiftDataRepository,
        private AmmunitionRepository $ammunitionRepository
    )
    {
    }

    public function getReport(DutyReportCombatShiftDTO $shift, Carbon $from, Carbon $to): FPVDutyReportDTO
    {
        $dronesRemaining = $this->FPVShiftDataRepository->getFPVDronesRemaining($shift->getCombatShiftID());
        $flights = $this->FPVShiftDataRepository->getFPVFlights($from, $to, $shift->getCombatShiftID());
        $ammoRemaining = $this->ammunitionRepository->getFPVAmmunitionRemaining($shift->getCombatShiftID());

        return new FPVDutyReportDTO(
            $shift,
            $dronesRemaining,
            $flights,
            $ammoRemaining
        );
    }
}
