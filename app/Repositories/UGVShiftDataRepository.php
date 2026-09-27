<?php

namespace App\Repositories;

use App\Collections\UGVDronesDTOCollection;
use App\Collections\UGVRacesDTOCollection;
use App\DTOs\UGVDroneDTO;
use App\DTOs\UGVRaceDTO;
use App\Enums\ShiftTypeEnum;
use App\Enums\UGVMissionResultEnum;
use App\Enums\UGVMissionTypeEnum;
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

    public function getRaces(Carbon $from, Carbon $to, int $combatShiftId): UGVRacesDTOCollection
    {
        $races = \DB::connection('mysql')
            ->table('ugv_races as ur')
            ->join('ugv_drones as ud', 'ur.ugv_drone_id', '=', 'ud.id')
            ->join('ugv_race_ammunition as ura', 'ur.id', '=', 'ura.ugv_race_id')
            ->join('ammunition as a', 'ura.ammunition_id', '=', 'a.id')
            ->where('ur.combat_shift_id', $combatShiftId)
            ->whereBetween('ur.start_time', [$from, $to])
            ->select([
                'ur.id as race_id',
                'ur.combat_shift_id as combat_shift_id',
                'ur.ugv_drone_id as drone_id',
                'ud.name as drone_name',
                'ud.serial_number as drone_serial_number',
                \DB::raw("
                    JSON_ARRAYAGG(
                        JSON_OBJECT(
                            'name', a.name,
                            'type', a.type
                        )
                    ) as ammunition
                "),
                'ur.coordinates as coordinates',
                'ur.start_time as start_time',
                'ur.end_time as end_time',
                'ur.mission_type as mission_type',
                'ur.result as result',
                'ur.comment as comment',
                'ur.shift_type as shift_type',
                'ur.video_path as video_path'
            ])
            ->groupBy(
                'ur.id',
                'ur.start_time',
                'ur.end_time',
                'ud.name',
                'ud.serial_number',
            )
            ->get();

        $collection = new UGVRacesDTOCollection();

        foreach ($races as $race) {
            $collection->add(new UGVRaceDTO(
                $race->race_id,
                $race->combat_shift_id,
                $race->drone_id,
                $race->drone_name,
                $race->drone_serial_number,
                json_decode($race->ammunition),
                $race->coordinates,
                UGVMissionTypeEnum::from($race->mission_type),
                UGVMissionResultEnum::from($race->result),
                $race->comment,
                ShiftTypeEnum::from($race->shift_type),
                $race->video_path,
                Carbon::parse($race->start_time),
                Carbon::parse($race->end_time)
            ));
        }

        return $collection;
    }
}
