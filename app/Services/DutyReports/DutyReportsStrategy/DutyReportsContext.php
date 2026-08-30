<?php


namespace App\Services\DutyReports\DutyReportsStrategy;

use App\DTOs\DutyReportCombatShiftDTO;

class DutyReportsContext
{
    public function __construct(private readonly DutyReportStrategyResolver $resolver)
    {
    }

    public function getReport(DutyReportCombatShiftDTO $shift)
    {
        return $this->resolver->resolve($shift->getType())->getReport($shift);
    }
}
