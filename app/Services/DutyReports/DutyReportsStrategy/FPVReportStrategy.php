<?php

namespace App\Services\DutyReports\DutyReportsStrategy;

use App\DTOs\DutyReportCombatShiftDTO;

class FPVReportStrategy implements DutyReportStrategy
{
    /**
     * @param DutyReportCombatShiftDTO $shift
     */
    public function __construct(
        private DutyReportCombatShiftDTO $shift,
    )
    {
    }

    public function getReport()
    {
        dd($this->shift->getPositionName());
    }
}
