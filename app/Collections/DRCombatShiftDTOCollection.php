<?php

namespace App\Collections;

use App\DTOs\DutyReportCombatShiftDTO;


class DRCombatShiftDTOCollection extends BaseTypedCollection
{
    protected function getTypedClassName(): string
    {
        return DutyReportCombatShiftDTO::class;
    }
}
