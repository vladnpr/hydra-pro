<?php

namespace App\Collections;

use App\DTOs\FPVFlightDTO;

class FPVFlightDTOCollection extends BaseTypedCollection
{
    protected function getTypedClassName(): string
    {
        return FPVFlightDTO::class;
    }
}
