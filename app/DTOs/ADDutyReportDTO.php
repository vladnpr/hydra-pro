<?php

namespace App\DTOs;

use App\Collections\ADDronesRemainingDTOCollection;
use App\Collections\ADFlightsDTOCollection;
use App\Collections\AmmunitionRemainingDTOCollection;

final readonly class ADDutyReportDTO
{
    public function __construct(
        private DutyReportCombatShiftDTO $combatShiftData,
        private ADDronesRemainingDTOCollection $dronesRemaining,
        private array $flights,
        private AmmunitionRemainingDTOCollection $ammunitionRemaining,
    )
    {
    }

    public function getCombatShiftData(): DutyReportCombatShiftDTO
    {
        return $this->combatShiftData;
    }

    public function getDronesRemaining(): ADDronesRemainingDTOCollection
    {
        return $this->dronesRemaining;
    }

    public function getFlights(): array
    {
        return $this->flights;
    }

    public function getAmmunitionRemaining(): AmmunitionRemainingDTOCollection
    {
        return $this->ammunitionRemaining;
    }
}
