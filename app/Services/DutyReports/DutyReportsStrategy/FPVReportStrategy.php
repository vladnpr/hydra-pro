<?php

namespace App\Services\DutyReports\DutyReportsStrategy;

use App\DTOs\DutyReportCombatShiftDTO;

class FPVReportStrategy implements DutyReportStrategy
{
    public function getReport(DutyReportCombatShiftDTO $shift)
    {
        $positionName = "ПДП {$shift->getPositionName()} {$shift->getType()->value}";
        $dronesRemaining = [];
    }

    private function getDronesRemaining()
    {
    }
}
