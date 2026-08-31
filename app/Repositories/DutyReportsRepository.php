<?php

namespace App\Repositories;

use App\Collections\FPVDronesRemainingDTOCollection;
use App\DTOs\FPVDronesRemainingDTO;
use App\Enums\CombatShiftStatus;
use App\DTOs\DutyReportCombatShiftDTO;
use App\Collections\DRCombatShiftDTOCollection;
use App\Enums\PositionTypesEnum;

class DutyReportsRepository
{
    public function getActiveShifts(): DRCombatShiftDTOCollection
    {
        $activeShifts = \DB::connection('mysql')
                ->table('combat_shifts as cs')
                ->join('positions as p', 'cs.position_id', '=', 'p.id')
                ->where('cs.status', CombatShiftStatus::OPENED->value)
                ->select([
                    "cs.id as combat_shift_id",
                    "p.name as position_name",
                    "p.type as type",
                    "cs.status as status",
                    "cs.user_id as user_id",
                    "cs.started_at as started_at"
                ])
                ->get();

        $collection = new DRCombatShiftDTOCollection();

        foreach ($activeShifts as $activeShift) {
            $activeShiftDTO = new DutyReportCombatShiftDTO(
                $activeShift->combat_shift_id,
                $activeShift->position_name,
                PositionTypesEnum::from($activeShift->type),
                CombatShiftStatus::from($activeShift->status),
                $activeShift->user_id,
                $activeShift->started_at
            );

            $collection->push($activeShiftDTO);
        }

        return $collection;
    }

    public function getDronesRemaining(int $combatShiftId): FPVDronesRemainingDTOCollection
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
}
