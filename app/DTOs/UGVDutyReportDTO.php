<?php

namespace App\DTOs;

use App\Collections\AmmunitionRemainingDTOCollection;
use App\Collections\UGVDronesDTOCollection;
use App\Collections\UGVRacesDTOCollection;

readonly class UGVDutyReportDTO
{
    public function __construct(
        private DutyReportCombatShiftDTO         $combatShiftData,
        private UGVDronesDTOCollection           $dronesRemaining,
        private array                            $races,
        private AmmunitionRemainingDTOCollection $ammoRemaining
    )
    {
    }

    public function getCombatShiftData(): DutyReportCombatShiftDTO
    {
        return $this->combatShiftData;
    }

    public function getDronesRemaining(): UGVDronesDTOCollection
    {
        return $this->dronesRemaining;
    }

    public function getRaces(): array
    {
        return $this->races;
    }

    public function getAmmoRemaining(): AmmunitionRemainingDTOCollection
    {
        return $this->ammoRemaining;
    }
}
