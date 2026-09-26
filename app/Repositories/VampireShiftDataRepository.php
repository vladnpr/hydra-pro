<?php

namespace App\Repositories;

use App\Collections\VampireDronesRemainingDTOCollection;
use App\Collections\VampireFlightsDTOCollection;
use App\DTOs\VampireDronesRemainingDTO;
use App\DTOs\VampireFlightDTO;
use App\Enums\DroneStatusEnum;
use App\Enums\ShiftTypeEnum;
use App\Enums\VampireMissionResultsEnum;
use App\Enums\VampireMissionTypesEnum;
use Carbon\Carbon;

class VampireShiftDataRepository
{
    public function getDronesRemaining(int $positionID): VampireDronesRemainingDTOCollection
    {
        $drones = \DB::connection('mysql')
            ->table('vampire_drones as vd')
            ->where('position_id', $positionID)
            ->where('lost_at', null)
            ->select([
                'vd.id as drone_id',
                'vd.name as drone_name',
                'vd.serial_number as drone_serial_number',
                'vd.shift_type as shift_type',
                'vd.status as drone_status',
            ])
            ->get();

        $collection = new VampireDronesRemainingDTOCollection();

        foreach ($drones as $drone) {
            $collection->add(new VampireDronesRemainingDTO(
                $drone->drone_id,
                $drone->drone_name,
                $drone->drone_serial_number,
                ShiftTypeEnum::from($drone->shift_type),
                DroneStatusEnum::from($drone->drone_status),
            ));
        }

        return $collection;
    }

    public function getFlights(Carbon $from, Carbon $to, int $combatShiftId): VampireFlightsDTOCollection
    {
        $flights = \DB::connection('mysql')
            ->table('vampire_flights as vf')
            ->join('vampire_drones as vd', 'vf.vampire_drone_id', '=', 'vd.id')
            ->join('vampire_flight_ammunition as vfa', 'vf.id', '=', 'vfa.vampire_flight_id')
            ->join('ammunition as a', 'vfa.ammunition_id', '=', 'a.id')
            ->whereBetween('start_time', [$from, $to])
            ->where('combat_shift_id', $combatShiftId)
            ->select(
                'vf.id as flight_id',
                'vf.start_time as start_time',
                'vf.end_time as end_time',
                'vf.coordinates as coordinates',
                'vf.mission_type as mission_type',
                'vf.result as result',
                'vf.shift_type as shift_type',
                'vf.comment as comment',
                'vf.video_path as video_path',
                'vd.name as drone_name',
                'vd.serial_number as drone_serial_number',
                \DB::raw("
                    JSON_ARRAYAGG(
                        JSON_OBJECT(
                            'name', a.name,
                            'type', a.type
                        )
                    ) as ammunition
                "),
            )
            ->groupBy(
                'vf.id',
                'vf.start_time',
                'vf.end_time',
                'vd.name',
                'vd.serial_number',
            )
            ->get();

        $collection = new VampireFlightsDTOCollection();

        foreach ($flights as $flight) {
            $collection->add(new VampireFlightDTO(
                $flight->flight_id,
                $flight->start_time,
                $flight->end_time,
                $flight->coordinates,
                VampireMissionTypesEnum::from($flight->mission_type),
                VampireMissionResultsEnum::from($flight->result),
                ShiftTypeEnum::from($flight->shift_type),
                $flight->comment,
                $flight->video_path,
                $flight->drone_name,
                $flight->drone_serial_number,
                json_decode($flight->ammunition)
            ));
        }

        return $collection;
    }
}
