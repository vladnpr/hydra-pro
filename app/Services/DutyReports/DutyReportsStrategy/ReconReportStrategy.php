<?php

namespace App\Services\DutyReports\DutyReportsStrategy;

use App\DTOs\DutyReportCombatShiftDTO;
use Carbon\Carbon;

class ReconReportStrategy implements DutyReportStrategy
{
    public function getReport(DutyReportCombatShiftDTO $shift, Carbon $from, Carbon $to)
    {
        // TODO: Implement getReport() method.
    }
}
