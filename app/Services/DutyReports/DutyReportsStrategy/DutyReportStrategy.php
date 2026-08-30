<?php

namespace App\Services\DutyReports\DutyReportsStrategy;

use App\DTOs\DutyReportCombatShiftDTO;

interface DutyReportStrategy
{
    public function getReport(DutyReportCombatShiftDTO $shift);
}
