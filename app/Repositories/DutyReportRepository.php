<?php

namespace App\Repositories;

use App\Models\CombatShift;

class DutyReportRepository
{
    /**
     * @param int $positionID
     * @return mixed
     */
    public function fpvInventoryData(int $positionID)
    {
        $data = \DB::table('combat_shifts as cs')
            ->select('cs.id')
            ->selectSub(function ($query) {
                $query->from('combat_shift_drone as csd')
                    ->join('drones as d', 'csd.drone_id', '=', 'd.id')
                    ->selectRaw("COALESCE(JSON_ARRAYAGG(JSON_OBJECT('name', d.name, 'quantity', csd.quantity)), '[]')")
                    ->whereColumn('csd.combat_shift_id', 'cs.id');
            }, 'drones')
            ->selectSub(function ($query) {
                $query->from('combat_shift_ammunition as csa')
                    ->join('ammunition as a', 'csa.ammunition_id', '=', 'a.id')
                    ->selectRaw("COALESCE(JSON_ARRAYAGG(JSON_OBJECT('name', a.name, 'quantity', csa.quantity)), '[]')")
                    ->whereColumn('csa.combat_shift_id', 'cs.id');
            }, 'ammunition')
            ->where('cs.position_id', $positionID)
            ->get();

        return $data->map(function ($item) {
            $item->drones = json_decode($item->drones, true);
            $item->ammunition = json_decode($item->ammunition, true);
            return $item;
        });
    }

    public function fpvFlightsStatsData(int $positionID, string $startDate, string $endDate)
    {
        return \DB::table('combat_shifts as cs')
            ->join('combat_shift_flights as csf', 'csf.combat_shift_id', '=', 'cs.id')
            ->where('cs.position_id', $positionID)
            ->whereBetween('csf.flight_time', [$startDate, $endDate])
            ->select('csf.mission', 'csf.result', \DB::raw('count(*) as count'))
            ->groupBy('csf.result', 'csf.mission')
            ->get();
    }
}
