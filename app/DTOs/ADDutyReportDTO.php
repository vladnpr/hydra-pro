<?php

namespace App\DTOs;

use App\Collections\ADDronesRemainingDTOCollection;
use App\Collections\ADFlightsDTOCollection;
use App\Collections\AmmunitionRemainingDTOCollection;

final readonly class ADDutyReportDTO
{
    public function __construct(
        private DutyReportCombatShiftDTO $shift,
        private ADDronesRemainingDTOCollection $dronesRemaining,
        private ADFlightsDTOCollection $flights,
        private AmmunitionRemainingDTOCollection $ammunitionRemaining,
    )
    {
    }

    public function getShift(): DutyReportCombatShiftDTO
    {
        return $this->shift;
    }

    public function getDronesRemaining(): ADDronesRemainingDTOCollection
    {
        return $this->dronesRemaining;
    }

    public function getFlights(): ADFlightsDTOCollection
    {
        return $this->flights;
    }

    public function getAmmunitionRemaining(): AmmunitionRemainingDTOCollection
    {
        return $this->ammunitionRemaining;
    }
}
