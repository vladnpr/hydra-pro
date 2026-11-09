<?php

namespace App\Presenters\Reports;

use App\Collections\DutyReportsDTOCollection;
use Carbon\Carbon;

readonly class DutyReportsListPresenter
{
    public function __construct(
        private Carbon $from,
        private Carbon $to,
        private DutyReportsDTOCollection $dutyReportsDTOCollection
    )
    {
    }

    public function getFrom(): Carbon
    {
        return $this->from;
    }

    public function getTo(): Carbon
    {
        return $this->to;
    }

    public function getDutyReportsDTOCollection(): DutyReportsDTOCollection
    {
        return $this->dutyReportsDTOCollection;
    }
}
