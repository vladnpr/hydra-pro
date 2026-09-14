<?php

namespace App\Collections;

use App\DTOs\FPVDronesRemainingDTO;

class FPVDronesRemainingDTOCollection extends BaseTypedCollection
{
    protected function getTypedClassName(): string
    {
        return FPVDronesRemainingDTO::class;
    }
}
