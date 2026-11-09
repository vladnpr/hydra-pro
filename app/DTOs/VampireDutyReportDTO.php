<?php

namespace App\DTOs;

use App\Collections\AmmunitionRemainingDTOCollection;
use App\Collections\VampireDronesRemainingDTOCollection;
use App\Collections\VampireFlightsDTOCollection;

final readonly class VampireDutyReportDTO
{
    public function __construct(
        private DutyReportCombatShiftDTO $combatShiftData,
        private VampireDronesRemainingDTOCollection $dronesRemaining,
        private array $flights,
        private AmmunitionRemainingDTOCollection $ammoRemaining,
    )
    {
    }

    public function getCombatShiftData(): DutyReportCombatShiftDTO
    {
        return $this->combatShiftData;
    }

    public function getDronesRemaining(): VampireDronesRemainingDTOCollection
    {
        return $this->dronesRemaining;
    }

    public function getFlights(): array
    {
        return $this->flights;
    }

    public function getAmmoRemaining(): AmmunitionRemainingDTOCollection
    {
        return $this->ammoRemaining;
    }
}
