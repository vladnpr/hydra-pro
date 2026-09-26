<?php

namespace App\Repositories;

use App\Collections\VampireDronesRemainingDTOCollection;
use App\DTOs\VampireDronesRemainingDTO;
use App\Enums\DroneStatusEnum;
use App\Enums\ShiftTypeEnum;

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
}
