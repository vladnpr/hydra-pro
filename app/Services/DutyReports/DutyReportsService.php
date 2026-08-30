<?php

namespace App\Services\DutyReports;


use App\Repositories\DutyReportsRepository;
use App\Services\DutyReports\DutyReportsStrategy\DutyReportsContext;

class DutyReportsService
{
    public function __construct(
        readonly private DutyReportsRepository $dutyReportsRepository,
        readonly private DutyReportsContext $reportStrategy
    )
    {
    }

    public function getReports()
    {
        $activeShifts = $this->dutyReportsRepository->getActiveShifts();

        foreach ($activeShifts as $activeShift) {
            $report = $this->reportStrategy->getReport($activeShift);
        }
    }
}
