<?php

namespace App\Collections;

use App\DTOs\FPVFlightDTO;

class FPVFlightDTOCollection extends BaseTypedCollection
{
    protected function getTypedClassNames(): array
    {
        return [
            FPVFlightDTO::class
        ];
    }
}
