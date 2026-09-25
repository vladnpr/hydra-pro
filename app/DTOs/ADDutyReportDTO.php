<?php

namespace App\DTOs;

use App\Collections\ADDronesRemainingDTOCollection;

class ADDutyReportDTO
{
    public function __construct(
        private readonly ADDronesRemainingDTOCollection $dronesRemaining
    )
    {
    }
}
