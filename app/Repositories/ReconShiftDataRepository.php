<?php

namespace App\Repositories;

use App\Collections\ReconDronesRemainingDTOCollection;
use App\DTOs\ReconDronesRemainingDTO;
use App\Enums\DroneStatusEnum;
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

    public function getFlights(Carbon $from, Carbon $to, int $combatShiftId)
    {
        //TODO: make query to get flights
    }
}
