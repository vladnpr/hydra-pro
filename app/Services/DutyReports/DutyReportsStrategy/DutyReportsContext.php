<?php


namespace App\Services\DutyReports\DutyReportsStrategy;

use App\Services\DutyReports\DutyReportsStrategy\DutyReportStrategy;

class DutyReportsContext
{
    private $context = [

    ];

    public function __construct(private DutyReportStrategy $strategy)
    {
        $this->strategy = $this->context[$this->strategy::class];
    }

    public function getReport()
    {
        return $this->strategy->getReport();
    }
}
