<?php

namespace App\Collections;

use App\DTOs\ReconDronesRemainingDTO;

class ReconDronesRemainingDTOCollection extends BaseTypedCollection
{
    protected function getTypedClassName(): string
    {
        return ReconDronesRemainingDTO::class;
    }
}
