<?php

namespace App\Repositories;

class DutyReportRepository
{
    public function fpvInventoryData(int $positionID)
    {
        $data = \DB::table('combat_shifts as cs')
            ->where('cs.position_id', $positionID)
            ->get();
    }
}
