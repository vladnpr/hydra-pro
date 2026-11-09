<?php

namespace App\Repositories;

use App\Collections\FPVDronesRemainingDTOCollection;
use App\Collections\FPVFlightDTOCollection;
use App\DTOs\FPVDronesRemainingDTO;
use App\DTOs\FPVFlightDTO;
use Carbon\Carbon;

class FPVShiftDataRepository
{
    public function getFPVDronesRemaining(int $combatShiftId): FPVDronesRemainingDTOCollection
    {
        $dronesRemaining = \DB::connection('mysql')
            ->table('combat_shifts as cs')
            ->join('combat_shift_drone as csd', 'csd.combat_shift_id', '=', 'cs.id')
            ->join('drones as d', 'd.id', '=', 'csd.drone_id')
            ->where('cs.id', $combatShiftId)
            ->select([
                "d.id as id",
                "d.name as name",
                "d.model as model",
                "csd.quantity as quantity"
            ])
            ->get();

        $collection = new FPVDronesRemainingDTOCollection();

        foreach ($dronesRemaining as $droneRemaining) {
            $collection->push(new FPVDronesRemainingDTO(
                $droneRemaining->id,
                $droneRemaining->name,
                $droneRemaining->model,
                $droneRemaining->quantity
            ));
        }

        return $collection;
    }


    /**
     * Get FPV flights for a combat shift.
     *
     * @param Carbon $from
     * @param Carbon $to
     * @param int $combatShiftId
     * @return FPVFlightDTOCollection
     */
    public function getFPVFlights(Carbon $from, Carbon $to, int $combatShiftId): FPVFlightDTOCollection
    {
        $flights = \DB::connection('mysql')
            ->table('combat_shift_flights as csf')
            ->join('drones as d', 'd.id', '=', 'csf.drone_id')
            ->join('ammunition as a', 'a.id', '=', 'csf.ammunition_id')
            ->select([
                'csf.id as id',
                'csf.flight_time as flight_time',
                'csf.coordinates as coordinates',
                'csf.mission as mission',
                'csf.result as mission_result',
                'd.name as drone_name',
                'a.name as ammunition_name',
                'csf.detonation as detonation',
                'csf.video_path as video_path',
            ])
            ->whereBetween('csf.flight_time', [$from, $to])
            ->where('csf.combat_shift_id', $combatShiftId)
            ->get();

        $collection = new FPVFlightDTOCollection();
        foreach ($flights as $flight) {
            $collection->push(
                new FPVFlightDTO(
                    $flight->id,
                    new Carbon($flight->flight_time),
                    $flight->coordinates,
                    $flight->mission,
                    $flight->mission_result,
                    $flight->drone_name,
                    $flight->ammunition_name,
                    $flight->detonation,
                    $flight->video_path,
                )
            );
        }

        return $collection;
    }
}
