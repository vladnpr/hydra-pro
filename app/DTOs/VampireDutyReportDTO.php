<?php

namespace App\DTOs;

use App\Collections\VampireDronesRemainingDTOCollection;

class VampireDutyReportDTO
{
    public function __construct(
        private DutyReportCombatShiftDTO $combatShift,
        private VampireDronesRemainingDTOCollection $dronesRemaining
    )
    {
    }
}
