<?php

namespace App\DTOs;

use App\Collections\AmmunitionRemainingDTOCollection;
use App\Collections\ReconDronesRemainingDTOCollection;
use App\Collections\ReconFlightDTOCollection;

final readonly class ReconDutyReportDTO
{
    public function __construct(
        private DutyReportCombatShiftDTO          $combatShift,
        private ReconDronesRemainingDTOCollection $dronesRemaining,
        private ReconFlightDTOCollection          $flightsData,
        private AmmunitionRemainingDTOCollection  $ammunitionRemainingData,
    )
    {
    }

    public function getAmmunitionRemainingData(): AmmunitionRemainingDTOCollection
    {
        return $this->ammunitionRemainingData;
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
