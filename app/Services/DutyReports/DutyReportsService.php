<?php

namespace App\Services\DutyReports;


use App\Repositories\CombatShiftsRepository;
use App\Services\DutyReports\DutyReportsStrategy\DutyReportsContext;
use Carbon\Carbon;

class DutyReportsService
{
    public function __construct(
        readonly private CombatShiftsRepository $dutyReportsRepository,
        readonly private DutyReportsContext     $reportStrategy
    )
    {
    }

    public function getReports(Carbon $from, Carbon $to)
    {
        $activeShifts = $this->dutyReportsRepository->getActiveShifts();

        foreach ($activeShifts as $activeShift) {
            $reportData = $this->reportStrategy->getReport($activeShift, $from, $to);
        }
    }
}
