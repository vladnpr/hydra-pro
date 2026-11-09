<?php

namespace App\Services\DutyReports\DutyReportsStrategy;

use App\Collections\FPVFlightDTOCollection;
use App\DTOs\DutyReportCombatShiftDTO;
use App\DTOs\FPVDutyReportDTO;
use App\DTOs\FPVFlightDTO;
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
        $flightsCollection = $this->FPVShiftDataRepository->getFPVFlights($from, $to, $shift->getCombatShiftID());
        $ammoRemaining = $this->ammunitionRepository->getAmmunitionRemaining($shift->getCombatShiftID());

        $flights = $this->flightsCalculate($flightsCollection);

        return new FPVDutyReportDTO(
            $shift,
            $dronesRemaining,
            $flights,
            $ammoRemaining
        );
    }

    private function flightsCalculate(FPVFlightDTOCollection $flightsCollection): array
    {
        $result['Всього вильотів'] = $flightsCollection->count();

        /** @var FPVFlightDTO $flight */
        foreach ($flightsCollection as $flight) {
            if (!array_key_exists($flight->getMissionResult(), $result)) {
                $result[$flight->getMissionResult()] = 1;
            } else {
                $result[$flight->getMissionResult()] = $result[$flight->getMissionResult()] + 1;
            }
        }

        return $result;
    }
}
