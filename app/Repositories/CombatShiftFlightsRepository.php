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
}
