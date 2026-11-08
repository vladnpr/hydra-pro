<?php

namespace App\Collections;

use App\DTOs\FPVDronesRemainingDTO;

class FPVDronesRemainingDTOCollection extends BaseTypedCollection
{
    protected function getTypedClassNames(): array
    {
        return [
            FPVDronesRemainingDTO::class
        ];
    }
}
