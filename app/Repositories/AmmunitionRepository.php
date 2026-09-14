<?php

namespace App\Repositories;

use App\Collections\AmmunitionRemainingDTOCollection;
use App\DTOs\AmmunitionRemainingDTO;
use App\Enums\PositionTypesEnum;

class AmmunitionRepository
{
    public function getFPVAmmunitionRemaining(int $shiftId): AmmunitionRemainingDTOCollection
    {
        $ammo = \DB::connection('mysql')
            ->table('combat_shift_ammunition as csa')
            ->join('ammunition as a', 'csa.ammunition_id', '=', 'a.id')
            ->select([
                'a.name as name',
                'csa.quantity as quantity',
                'a.status as status',
                'a.type as shift_type',
            ])
            ->where('csa.combat_shift_id', $shiftId)
            ->where('a.status', 1)
            ->get();

        $dtoCollection = new AmmunitionRemainingDTOCollection();

        foreach ($ammo as $item) {
            $dto = new AmmunitionRemainingDTO(
                $item->name,
                $item->quantity,
                $item->status,
                PositionTypesEnum::from($item->shift_type),
            );

            $dtoCollection->add($dto);
        }

        return $dtoCollection;
    }
}
