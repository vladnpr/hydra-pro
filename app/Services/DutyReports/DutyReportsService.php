<?php

namespace App\Services\DutyReports;


use App\Repositories\DutyReportsRepository;
use App\Services\DutyReports\DutyReportsStrategy\DutyReportsContext;

class DutyReportsService
{
    public function __construct(
        readonly private DutyReportsRepository $dutyReportsRepository,
    )
    {
    }

    public function getReports()
    {
        $activeShifts = $this->dutyReportsRepository->getActiveShifts();

        foreach ($activeShifts as $activeShift) {
            $reportStrategy = new DutyReportsContext($activeShift);
            $report = $reportStrategy->getReport();
        }
    }
}
