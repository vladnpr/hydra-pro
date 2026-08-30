<?php

namespace App\Services\DutyReports;


use App\Repositories\DutyReportsRepository;

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

        }
    }
}
