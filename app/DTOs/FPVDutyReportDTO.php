<?php

namespace App\DTOs;

use App\Collections\AmmunitionRemainingDTOCollection;
use App\Collections\FPVDronesRemainingDTOCollection;
use App\Collections\FPVFlightDTOCollection;

final readonly class FPVDutyReportDTO
{
    public function __construct(
        private DutyReportCombatShiftDTO $combatShiftData,
        private FPVDronesRemainingDTOCollection $dronesRemainingCollection,
        private FPVFlightDTOCollection $flights,
        private AmmunitionRemainingDTOCollection $ammoRemaining
    )
    {
    }

    public function getAmmoRemaining(): AmmunitionRemainingDTOCollection
    {
        return $this->ammoRemaining;
    }

    public function getCombatShiftData(): DutyReportCombatShiftDTO
    {
        return $this->combatShiftData;
    }

    public function getDronesRemainingCollection(): FPVDronesRemainingDTOCollection
    {
        return $this->dronesRemainingCollection;
    }

    public function getFlights(): FPVFlightDTOCollection
    {
        return $this->flights;
    }
}
