<?php

namespace App\Services\DutyReports\DutyReportsStrategy;

use App\DTOs\DutyReportCombatShiftDTO;

interface DutyReportStrategy
{
    /**
     * @param DutyReportCombatShiftDTO $shift
     */
    public function __construct(DutyReportCombatShiftDTO $shift);
    public function getReport();
}
