<?php

namespace App\Repositories;

use App\Collections\ReconDronesRemainingDTOCollection;
use App\Collections\ReconFlightDTOCollection;
use App\DTOs\ReconDronesRemainingDTO;
use App\DTOs\ReconFlightDTO;
use App\Enums\DroneStatusEnum;
use App\Enums\ReconMissionResultsEnum;
use App\Enums\ReconMissionTypesEnum;
use App\Enums\ShiftTypeEnum;
use Carbon\Carbon;

class ReconShiftDataRepository
{
    public function getRemainingDrones(int $combatShiftID): ReconDronesRemainingDTOCollection
    {
        $dronesRemaining = \DB::connection('mysql')
            ->table('combat_shifts as cs')
            ->join('recon_drones as rd', 'cs.position_id', '=', 'rd.position_id')
            ->where('cs.id', $combatShiftID)
            ->where('rd.status', DroneStatusEnum::ACTIVE->value)
            ->select([
                'rd.id as drone_id',
                'rd.name as drone_name',
                'rd.serial_number as drone_serial_number',
                'rd.shift_type as drone_shift_type',
                'rd.status as drone_status',
            ])
            ->get();
        $collection = new ReconDronesRemainingDTOCollection();

        foreach ($dronesRemaining as $drone) {
            $collection->push(new ReconDronesRemainingDTO(
                $drone->drone_id,
                $drone->drone_name,
                $drone->drone_serial_number,
                ShiftTypeEnum::from($drone->drone_shift_type),
                DroneStatusEnum::from($drone->drone_status)
            ));
        }

        return $collection;
    }

    public function getFlights(Carbon $from, Carbon $to, int $combatShiftId): ReconFlightDTOCollection
    {
        $flights = \DB::connection('mysql')
            ->table('recon_flights as rf')
            ->join('recon_drones as rd', 'rf.recon_drone_id', '=', 'rd.id')
            ->whereBetween('rf.flight_time', [$from, $to])
            ->where('rf.combat_shift_id', $combatShiftId)
            ->select([
                'rf.id as flight_id',
                'rf.coordinates as flight_coordinates',
                'rd.name as drone_name',
                'rd.serial_number as drone_serial_number',
                'rf.flight_time as start_time',
                'rf.landing_time as landing_time',
                'rf.mission_type as mission_type',
                'rf.target_name as target_name',
                'rf.result as result',
                'rf.description as description',
            ])
            ->get();

        $collection = new ReconFlightDTOCollection();

        foreach ($flights as $flight) {
            $collection->push(new ReconFlightDTO(
                $flight->flight_id,
                $flight->flight_coordinates,
                $flight->drone_name,
                $flight->drone_serial_number,
                new Carbon($flight->start_time),
                new Carbon($flight->landing_time),
                ReconMissionTypesEnum::from($flight->mission_type),
                $flight->target_name,
                ReconMissionResultsEnum::from($flight->result),
                $flight->description,
            ));
        }

        return $collection;
    }
}
