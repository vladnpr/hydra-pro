<?php


namespace App\Services\DutyReports\DutyReportsStrategy;

use App\DTOs\DutyReportCombatShiftDTO;
use Carbon\Carbon;

class DutyReportsContext
{
    public function __construct(private readonly DutyReportStrategyResolver $resolver)
    {
    }

    public function getReport(DutyReportCombatShiftDTO $shift, Carbon $from, Carbon $to)
    {
        return $this->resolver->resolve($shift->getType())->getReport($shift, $from, $to);
    }
}
