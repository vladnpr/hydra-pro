<?php

namespace App\DTOs;

use App\Collections\ReconDronesRemainingDTOCollection;
use App\Collections\ReconFlightDTOCollection;

final readonly class ReconDutyReportDTO
{
    public function __construct(
        private DutyReportCombatShiftDTO          $combatShift,
        private ReconDronesRemainingDTOCollection $dronesRemaining,
        private ReconFlightDTOCollection          $flightsData
    )
    {
    }

    public function getFlightsData(): ReconFlightDTOCollection
    {
        return $this->flightsData;
    }

    public function getDronesRemaining(): ReconDronesRemainingDTOCollection
    {
        return $this->dronesRemaining;
    }

    public function getCombatShift(): DutyReportCombatShiftDTO
    {
        return $this->combatShift;
    }
}
