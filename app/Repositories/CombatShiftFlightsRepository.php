<?php

namespace App\Repositories;

use Carbon\Carbon;

class CombatShiftFlightsRepository
{
    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllFlights(): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\CombatShiftFlight::all();
    }

    public function getFlightsByShift(int $shiftID): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\CombatShiftFlight::where('combat_shift_id', $shiftID)->get();
    }

    /**
     * @param int $shiftID
     * @param Carbon $dateFrom
     * @param Carbon $dateTo
     * @return \Illuminate\Support\Collection
     */
    public function getSpendingByFlightsDate(int $shiftID, Carbon $dateFrom, Carbon $dateTo): \Illuminate\Support\Collection
    {
        return \DB::table('combat_shift_flights as csf')
            ->join('drones as d', 'csf.drone_id', '=', 'd.id')
            ->leftJoin('ammunition as a', 'csf.ammunition_id', '=', 'a.id')
            ->where('combat_shift_id', $shiftID)
            ->whereBetween('flight_time', [$dateFrom, $dateTo])
            ->where('csf.result', '!=', 'відпрацювали (повернули борт)')
            ->selectRaw("CONCAT(d.name, ' (', d.model, ')') as drone_name, IFNULL(a.name, 'Без БК (Логістика)') as ammunition_name")
            ->get();
    }
}
