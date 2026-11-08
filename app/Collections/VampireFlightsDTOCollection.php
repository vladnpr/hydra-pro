<?php

namespace App\Collections;

use App\DTOs\VampireFlightDTO;

class VampireFlightsDTOCollection extends BaseTypedCollection
{

    protected function getTypedClassNames(): array
    {
        return [
            VampireFlightDTO::class
        ];
    }
}
