<?php

namespace App\Services\DutyReports\DutyReportsStrategy;

use App\Collections\ReconFlightDTOCollection;
use App\DTOs\DutyReportCombatShiftDTO;
use App\DTOs\ReconDutyReportDTO;
use App\DTOs\ReconFlightDTO;
use App\Enums\ReconMissionResultsEnum;
use App\Repositories\AmmunitionRepository;
use App\Repositories\ReconShiftDataRepository;
use Carbon\Carbon;

readonly class ReconReportStrategy implements DutyReportStrategy
{
    public function __construct(
        private ReconShiftDataRepository $reconShiftDataRepository,
        private AmmunitionRepository     $ammunitionRepository,
    )
    {
    }

    public function getReport(DutyReportCombatShiftDTO $shift, Carbon $from, Carbon $to): ReconDutyReportDTO
    {
        $dronesRemainingData = $this->reconShiftDataRepository->getRemainingDrones($shift->getCombatShiftID());
        $flightsData = $this->reconShiftDataRepository->getFlights($from, $to, $shift->getCombatShiftID());
        $ammunitionRemainingData = $this->ammunitionRepository->getAmmunitionRemaining($shift->getCombatShiftID());

        $flights = $this->flightsCalculate($flightsData);

        return new ReconDutyReportDTO(
            $shift,
            $dronesRemainingData,
            $flights,
            $ammunitionRemainingData
        );
    }

    private function flightsCalculate(ReconFlightDTOCollection $flightsCollection): array
    {
        $result['Всього вильотів'] = $flightsCollection->count();

        /** @var ReconFlightDTO $flight */
        foreach ($flightsCollection as $flight) {
            if ($flight->getResult()->value == ReconMissionResultsEnum::BOARD_LOOSED->value) {
                if (array_key_exists($flight->getResult()->value, $result)) {
                    $result[$flight->getResult()->value] = $result[$flight->getResult()->value] + 1;
                } else {
                    $result[$flight->getResult()->value] = 1;
                }
            }
        }

        return $result;
    }
}
