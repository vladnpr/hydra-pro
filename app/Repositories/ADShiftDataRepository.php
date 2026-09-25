<?php

namespace App\Repositories;

use App\Collections\ADDronesRemainingDTOCollection;
use App\Collections\ADFlightsDTOCollection;
use App\Collections\AmmunitionRemainingDTOCollection;
use App\DTOs\ADDronesRemainingDTO;
use App\DTOs\ADFlightsDTO;
use App\DTOs\AmmunitionRemainingDTO;
use App\Enums\PositionTypesEnum;
use Carbon\Carbon;

class ADShiftDataRepository
{
    const AMMO_STATUS = 1;

    public function getDronesRemaining(int $shiftId): ADDronesRemainingDTOCollection
    {
        $dronesRemaining = \DB::connection('mysql')
            ->table('combat_shift_air_defence_drone as csad')
            ->join('air_defence_drones as add', 'add.id', '=', 'csad.air_defence_drone_id')
            ->where('csad.combat_shift_id', '=', $shiftId)
            ->select([
                'add.id as drone_id',
                'add.name as drone_name',
                'add.model as model',
                'add.status as drone_status',
                'csad.quantity as quantity',
            ])
            ->get();
        $collection = new ADDronesRemainingDTOCollection();

        foreach ($dronesRemaining as $drone) {
            $collection->add(new ADDronesRemainingDTO(
                $drone->drone_id,
                $drone->drone_name,
                $drone->model,
                $drone->drone_status,
                $drone->quantity,
            ));
        }

        return $collection;
    }

    public function getFlights(Carbon $from, Carbon $to): ADFlightsDTOCollection
    {
        //TODO: need add combat_shift_id into air_defence_flights table
        $flights = \DB::connection('mysql')
            ->table('air_defence_flights as adf')
            ->join('air_defence_ammunition as ada', 'adf.air_defence_ammunition_id', '=', 'ada.id')
            ->join('air_defence_drones as add', 'adf.air_defence_drone_id', '=', 'add.id')
            ->whereBetween('adf.start_time', [$from, $to])
            ->where('adf.deleted_at', null)
            ->select([
                'adf.id as flight_id',
                'adf.coordinates as coordinate',
                'adf.start_time as start_time',
                'adf.end_time as end_time',
                'adf.result as result',
                'adf.air_defence_ammunition_id as ammunition_id',
                'adf.air_defence_drone_id as drone_id',
                'ada.name as ammunition_name',
                'adf.detonation as detonation',
                'adf.video_path as video_path',
                'add.name as drone_name',
            ])
            ->get();

        $collection = new ADFlightsDTOCollection();

        foreach ($flights as $flight) {
            $collection->add(new ADFlightsDTO(
                $flight->flight_id,
                $flight->coordinate,
                $flight->start_time,
                $flight->end_time,
                $flight->result,
                $flight->ammunition_id,
                $flight->drone_id,
                $flight->ammunition_name,
                $flight->detonation,
                $flight->video_path,
                $flight->drone_name,
            ));
        }

        return $collection;
    }

    public function getAmmunitionRemaining(int $shiftID): AmmunitionRemainingDTOCollection
    {
        //TODO: refactor usage ammunition table from air defence to common as in fpv
        $ammunition = \DB::connection('mysql')
            ->table('combat_shift_air_defence_ammunition as cad')
            ->join('air_defence_ammunition as csa', 'csa.id', '=', 'cad.air_defence_ammunition_id')
            ->where('cad.combat_shift_id', $shiftID)
            ->select([
                'csa.name as ammunition_name',
                'cad.quantity as quantity',
            ])
            ->get();

        $collection = new AmmunitionRemainingDTOCollection();

        foreach ($ammunition as $item) {
            $collection->add(new AmmunitionRemainingDTO(
                $item->ammunition_name,
                $item->quantity,
                self::AMMO_STATUS,
                PositionTypesEnum::from(PositionTypesEnum::AIR_DEFENCE->value)
            ));
        }

        return $collection;
    }
}
