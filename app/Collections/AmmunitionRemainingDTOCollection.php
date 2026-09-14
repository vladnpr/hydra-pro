<?php

namespace App\Collections;

use App\DTOs\AmmunitionRemainingDTO;

class AmmunitionRemainingDTOCollection extends BaseTypedCollection
{
    protected function getTypedClassName(): string
    {
        return AmmunitionRemainingDTO::class;
    }
}
