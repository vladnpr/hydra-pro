<?php

namespace App\Services\DutyReports;


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

    public function getReports(Carbon $from, Carbon $to): array
    {
        $activeShifts = $this->dutyReportsRepository->getActiveShifts();
        $reportData = [];

        foreach ($activeShifts as $activeShift) {
            $reportData[] = $this->reportStrategy->getReport($activeShift, $from, $to);
        }

        return $reportData;
    }
}
