<?php

namespace App\Repositories;

use App\Collections\ADDronesRemainingDTOCollection;
use App\DTOs\ADDronesRemainingDTO;

class AirDefenceShiftDataRepository
{
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
}
