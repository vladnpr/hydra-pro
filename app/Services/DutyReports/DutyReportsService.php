<?php

namespace App\Services\DutyReports;


use App\Collections\DutyReportsDTOCollection;
use App\Repositories\CombatShiftsRepository;
use App\Services\DutyReports\DutyReportsStrategy\DutyReportsContext;
use Carbon\Carbon;

final readonly class DutyReportsService
{
    public function __construct(
        private CombatShiftsRepository $dutyReportsRepository,
        private DutyReportsContext     $reportStrategy
    )
    {
    }

    public function getReportsData(Carbon $from, Carbon $to): DutyReportsDTOCollection
    {
        $activeShifts = $this->dutyReportsRepository->getActiveShifts();
        $reportData = new DutyReportsDTOCollection();

        foreach ($activeShifts as $activeShift) {
            $reportData->add($this->reportStrategy->getReport($activeShift, $from, $to));
        }

        return $reportData;
    }
}
