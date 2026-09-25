<?php

namespace App\DTOs;

use App\Collections\ADDronesRemainingDTOCollection;
use App\Collections\ADFlightsDTOCollection;

class ADDutyReportDTO
{
    public function __construct(
        private readonly ADDronesRemainingDTOCollection $dronesRemaining,
        private readonly ADFlightsDTOCollection $flights,
    )
    {
    }
}
