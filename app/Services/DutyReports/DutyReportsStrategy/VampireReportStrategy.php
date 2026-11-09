<?php

namespace App\Services\DutyReports\DutyReportsStrategy;

use App\Collections\VampireFlightsDTOCollection;
use App\DTOs\DutyReportCombatShiftDTO;
use App\DTOs\VampireDutyReportDTO;
use App\DTOs\VampireFlightDTO;
use App\Repositories\AmmunitionRepository;
use App\Repositories\VampireShiftDataRepository;
use Carbon\Carbon;

final readonly class VampireReportStrategy implements DutyReportStrategy
{
    public function __construct(
        private VampireShiftDataRepository $vampireShiftDataRepository,
        private AmmunitionRepository $ammunitionRepository,
    )
    {
    }

    public function getReport(DutyReportCombatShiftDTO $shift, Carbon $from, Carbon $to): VampireDutyReportDTO
    {
        $dronesRemaining = $this->vampireShiftDataRepository->getDronesRemaining($shift->getPositionID());
        $flightsDataCollection = $this->vampireShiftDataRepository->getFlights($from, $to, $shift->getCombatShiftID());
        $ammoRemaining = $this->ammunitionRepository->getAmmunitionRemaining($shift->getCombatShiftID());

        $flights = $this->flightsCalculate($flightsDataCollection);

        return new VampireDutyReportDTO(
            $shift,
            $dronesRemaining,
            $flights,
            $ammoRemaining,
        );
    }

    private function flightsCalculate(VampireFlightsDTOCollection $flightsCollection): array
    {
        $result['Всього вильотів'] = $flightsCollection->count();

        /** @var VampireFlightDTO $flight */
        foreach ($flightsCollection as $flight) {
            if (!array_key_exists($flight->getResult()->label(), $result)) {
                $result[$flight->getResult()->label()] = 1;
            } else {
                $result[$flight->getResult()->label()] = $result[$flight->getResult()->label()] + 1;
            }

            if (!array_key_exists($flight->getMissionType()->label(), $result)) {
                $result[$flight->getMissionType()->label()] = 1;
            } else {
                $result[$flight->getMissionType()->label()] = $result[$flight->getMissionType()->label()] + 1;
            }

        }

        return $result;
    }
}
