<?php

namespace App\Presenters\Reports;

use App\Collections\DutyReportsDTOCollection;

readonly class DutyReportsListPresenter
{
    public function __construct(
        private DutyReportsDTOCollection $dutyReportsDTOCollection
    )
    {
    }

    public function getDutyReportsDTOCollection(): DutyReportsDTOCollection
    {
        return $this->dutyReportsDTOCollection;
    }
}
