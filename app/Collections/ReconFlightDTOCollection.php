<?php

namespace App\Collections;

use App\DTOs\ReconFlightDTO;

class ReconFlightDTOCollection extends BaseTypedCollection
{
    protected function getTypedClassName(): string
    {
        return ReconFlightDTO::class;
    }
}
