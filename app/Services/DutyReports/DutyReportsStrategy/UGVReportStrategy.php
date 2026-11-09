<?php

namespace App\Services\DutyReports\DutyReportsStrategy;

use App\Collections\UGVRacesDTOCollection;
use App\DTOs\DutyReportCombatShiftDTO;
use App\DTOs\UGVDutyReportDTO;
use App\DTOs\UGVRaceDTO;
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
        $racesCollection = $this->shiftDataRepository->getRaces($from, $to, $shift->getCombatShiftID());
        $ammoRemaining = $this->ammunitionRepository->getAmmunitionRemaining($shift->getCombatShiftID());

        $races = $this->racesCalculate($racesCollection);

        return new UGVDutyReportDTO(
            $shift,
            $UGVDronesRemaining,
            $races,
            $ammoRemaining
        );
    }

    private function racesCalculate(UGVRacesDTOCollection $racesCollection): array
    {
        $result['Всього рейсів'] = $racesCollection->count();

        /** @var UGVRaceDTO $race */
        foreach ($racesCollection as $race) {
            if (!array_key_exists($race->getResult()->label(), $result)) {
                $result[$race->getResult()->label()] = 1;
            } else {
                $result[$race->getResult()->label()] = $result[$race->getResult()->label()] + 1;
            }

            if (!array_key_exists($race->getMissionType()->label(), $result)) {
                $result[$race->getMissionType()->label()] = 1;
            } else {
                $result[$race->getMissionType()->label()] = $result[$race->getMissionType()->label()] + 1;
            }

        }

        return $result;
    }
}
