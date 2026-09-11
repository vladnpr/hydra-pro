<?php

namespace App\DTOs;

use App\Collections\ReconDronesRemainingDTOCollection;

final class ReconDutyReportDTO
{
    public function __construct(
        private readonly DutyReportCombatShiftDTO $combatShift,
        private readonly ReconDronesRemainingDTOCollection $dronesRemaining,
    )
    {
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
