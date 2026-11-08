<?php

namespace App\Collections;

use App\DTOs\ReconDronesRemainingDTO;

class ReconDronesRemainingDTOCollection extends BaseTypedCollection
{
    protected function getTypedClassNames(): array
    {
        return [
            ReconDronesRemainingDTO::class
        ];
    }
}
