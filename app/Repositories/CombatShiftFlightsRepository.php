<?php

namespace App\Repositories;

class CombatShiftFlightsRepository
{
    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllFlights(): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\CombatShiftFlight::all();
    }

    public function getFlightsByShift(int $shiftId): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\CombatShiftFlight::where('combat_shift_id', $shiftId)->get();
    }
}
