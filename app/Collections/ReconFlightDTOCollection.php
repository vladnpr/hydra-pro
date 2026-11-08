<?php

namespace App\Collections;

use App\DTOs\ReconFlightDTO;

class ReconFlightDTOCollection extends BaseTypedCollection
{
    protected function getTypedClassNames(): array
    {
        return [
            ReconFlightDTO::class
        ];
    }
}
