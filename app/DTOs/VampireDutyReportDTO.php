<?php

namespace App\DTOs;

use App\Collections\AmmunitionRemainingDTOCollection;
use App\Collections\VampireDronesRemainingDTOCollection;
use App\Collections\VampireFlightsDTOCollection;

final readonly class VampireDutyReportDTO
{
    public function __construct(
        private DutyReportCombatShiftDTO $combatShift,
        private VampireDronesRemainingDTOCollection $dronesRemaining,
        private VampireFlightsDTOCollection $flights,
        private AmmunitionRemainingDTOCollection $ammoRemaining,
    )
    {
    }

    public function getCombatShift(): DutyReportCombatShiftDTO
    {
        return $this->combatShift;
    }

    public function getDronesRemaining(): VampireDronesRemainingDTOCollection
    {
        return $this->dronesRemaining;
    }

    public function getFlights(): VampireFlightsDTOCollection
    {
        return $this->flights;
    }

    public function getAmmoRemaining(): AmmunitionRemainingDTOCollection
    {
        return $this->ammoRemaining;
    }
}
