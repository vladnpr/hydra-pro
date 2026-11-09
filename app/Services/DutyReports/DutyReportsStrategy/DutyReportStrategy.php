<?php

namespace App\Services\DutyReports\DutyReportsStrategy;

use App\DTOs\DutyReportCombatShiftDTO;
use Carbon\Carbon;

interface DutyReportStrategy
{
    public function getReport(DutyReportCombatShiftDTO $shift, Carbon $from, Carbon $to);
}
