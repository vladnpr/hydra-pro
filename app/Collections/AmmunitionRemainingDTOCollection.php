<?php

namespace App\Collections;

use App\DTOs\AmmunitionRemainingDTO;

class AmmunitionRemainingDTOCollection extends BaseTypedCollection
{
    protected function getTypedClassNames(): array
    {
        return [
                AmmunitionRemainingDTO::class
        ];
    }
}
