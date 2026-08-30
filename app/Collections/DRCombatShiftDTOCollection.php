<?php

namespace App\Collections;

use Illuminate\Support\Collection;
use App\DTOs\DutyReportCombatShiftDTO;


class DRCombatShiftDTOCollection extends Collection
{
    /**
     * @param $items
     * @throws \Throwable
     */
    public function __construct($items = [])
    {
        foreach ($items as $item) {
            throw_if(!($item instanceof DutyReportCombatShiftDTO), 'Item is not an instance of DutyReportCombatShiftDTO');
        }

        parent::__construct($items);
    }
}
