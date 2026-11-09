<?php

namespace App\Collections;

use App\DTOs\ReconDroneRemainingDTO;

class ReconDronesRemainingDTOCollection extends BaseTypedCollection
{
    protected function getTypedClassNames(): array
    {
        return [
            ReconDroneRemainingDTO::class
        ];
    }
}
