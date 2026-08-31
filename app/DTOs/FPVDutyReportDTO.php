<?php

namespace App\DTOs;

use App\Collections\FPVDronesRemainingDTOCollection;

final readonly class FPVDutyReportDTO
{
    public function __construct(
        private DutyReportCombatShiftDTO $combatShiftData,
        private FPVDronesRemainingDTOCollection $dronesRemainingCollection,
    )
    {
    }
}
