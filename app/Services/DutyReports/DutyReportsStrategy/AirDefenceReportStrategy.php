<?php

namespace App\Services\DutyReports\DutyReportsStrategy;

use App\Collections\ADFlightsDTOCollection;
use App\DTOs\ADDutyReportDTO;
use App\DTOs\ADFlightsDTO;
use App\DTOs\DutyReportCombatShiftDTO;
use App\Repositories\ADShiftDataRepository;
use Carbon\Carbon;

readonly class AirDefenceReportStrategy implements DutyReportStrategy
{
    public function __construct(
        private ADShiftDataRepository $airDefenceShiftDataRepository
    )
    {

    }

    public function getReport(DutyReportCombatShiftDTO $shift, Carbon $from, Carbon $to): ADDutyReportDTO
    {
        $dronesRemaining = $this->airDefenceShiftDataRepository->getDronesRemaining($shift->getCombatShiftID());
        $flightsDataCollection = $this->airDefenceShiftDataRepository->getFlights($from, $to);
        $ammunition = $this->airDefenceShiftDataRepository->getAmmunitionRemaining($shift->getCombatShiftID());

        $flights = $this->flightsCalculate($flightsDataCollection);
        return new ADDutyReportDTO(
            $shift,
            $dronesRemaining,
            $flights,
            $ammunition
        );
    }

    private function flightsCalculate(ADFlightsDTOCollection $flightsCollection): array
    {
        $result['Всього вильотів'] = $flightsCollection->count();

        /** @var ADFlightsDTO $flight */
        foreach ($flightsCollection as $flight) {
            if (!array_key_exists($flight->getResult(), $result)) {
                $result[$flight->getResult()] = 1;
            } else {
                $result[$flight->getResult()] = $result[$flight->getResult()] + 1;
            }
        }

        return $result;
    }
}
