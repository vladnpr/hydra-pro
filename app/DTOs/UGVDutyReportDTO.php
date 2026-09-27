<?php

namespace App\DTOs;

use App\Collections\AmmunitionRemainingDTOCollection;
use App\Collections\UGVDronesDTOCollection;
use App\Collections\UGVRacesDTOCollection;

class UGVDutyReportDTO
{
    public function __construct(
        private DutyReportCombatShiftDTO $shift,
        private UGVDronesDTOCollection $UGVDronesRemaining,
        private UGVRacesDTOCollection $races,
        private AmmunitionRemainingDTOCollection $ammoRemaining
    )
    {
    }

    public function getShift(): DutyReportCombatShiftDTO
    {
        return $this->shift;
    }

    public function setShift(DutyReportCombatShiftDTO $shift): void
    {
        $this->shift = $shift;
    }

    public function getUGVDronesRemaining(): UGVDronesDTOCollection
    {
        return $this->UGVDronesRemaining;
    }

    public function setUGVDronesRemaining(UGVDronesDTOCollection $UGVDronesRemaining): void
    {
        $this->UGVDronesRemaining = $UGVDronesRemaining;
    }

    public function getRaces(): UGVRacesDTOCollection
    {
        return $this->races;
    }

    public function setRaces(UGVRacesDTOCollection $races): void
    {
        $this->races = $races;
    }

    public function getAmmoRemaining(): AmmunitionRemainingDTOCollection
    {
        return $this->ammoRemaining;
    }

    public function setAmmoRemaining(AmmunitionRemainingDTOCollection $ammoRemaining): void
    {
        $this->ammoRemaining = $ammoRemaining;
    }
}
