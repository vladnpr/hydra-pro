<?php

namespace App\DTOs;

use App\Collections\UGVDronesDTOCollection;

class UGVDutyReportDTO
{
    public function __construct(
        private DutyReportCombatShiftDTO $shift,
        private UGVDronesDTOCollection $UGVDronesRemaining,
    )
    {
    }
}
