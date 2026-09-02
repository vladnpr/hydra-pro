<?php

namespace App\DTOs;

use App\Collections\FPVDronesRemainingDTOCollection;
use App\Collections\FPVFlightDTOCollection;

final readonly class FPVDutyReportDTO
{
    public function __construct(
        private DutyReportCombatShiftDTO $combatShiftData,
        private FPVDronesRemainingDTOCollection $dronesRemainingCollection,
        private FPVFlightDTOCollection $flights
    )
    {
    }

    public function getCombatShiftData(): DutyReportCombatShiftDTO
    {
        return $this->combatShiftData;
    }

    public function getDronesRemainingCollection(): FPVDronesRemainingDTOCollection
    {
        return $this->dronesRemainingCollection;
    }

    public function getFlights(): FPVFlightDTOCollection
    {
        return $this->flights;
    }
}
