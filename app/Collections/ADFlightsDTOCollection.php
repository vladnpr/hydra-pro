<?php

namespace App\Collections;

use App\DTOs\ADFlightsDTO;

class ADFlightsDTOCollection extends BaseTypedCollection
{
    protected function getTypedClassNames(): array
    {
        return [
            ADFlightsDTO::class
        ];
    }
}
