<?php

namespace App\DTOs;

use App\Collections\AmmunitionRemainingDTOCollection;
use App\Collections\ReconDronesRemainingDTOCollection;
use App\Collections\ReconFlightDTOCollection;

final readonly class ReconDutyReportDTO
{
    public function __construct(
        private DutyReportCombatShiftDTO          $combatShiftData,
        private ReconDronesRemainingDTOCollection $dronesRemaining,
        private array                             $flights,
        private AmmunitionRemainingDTOCollection  $ammunitionRemainingData,
    )
    {
    }

    public function getAmmunitionRemainingData(): AmmunitionRemainingDTOCollection
    {
        return $this->ammunitionRemainingData;
    }

    public function getFlights(): array
    {
        return $this->flights;
    }

    public function getDronesRemaining(): ReconDronesRemainingDTOCollection
    {
        return $this->dronesRemaining;
    }

    public function getCombatShiftData(): DutyReportCombatShiftDTO
    {
        return $this->combatShiftData;
    }
}
