<?php

namespace App\Repositories;

use App\Collections\UGVDronesDTOCollection;
use App\DTOs\UGVDroneDTO;
use Carbon\Carbon;

class UGVShiftDataRepository
{
    public function getUGVRemaining(int $positionID): UGVDronesDTOCollection
    {
        $UGVDronesRemaining = \DB::connection('mysql')
            ->table('ugv_drones')
            ->where([
                'position_id' => $positionID,
                'lost_at' => null,
                'status' => 'active',
                'deleted_at' => null,
            ])
            ->get();

        $collection = new UGVDronesDTOCollection();

        foreach ($UGVDronesRemaining as $item) {
            $collection->add(new UGVDroneDTO(
                $item->id,
                $item->name,
                $item->position_id,
                $item->status,
                Carbon::parse($item->created_at),
                Carbon::parse($item->updated_at),
                $item->lost_at ? Carbon::parse($item->lost_at) : null,
                $item->deleted_at ? Carbon::parse($item->deleted_at) : null,
            ));
        }

        return $collection;
    }
}
