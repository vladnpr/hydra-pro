<?php

namespace App\Collections;

use App\DTOs\VampireFlightDTO;

class VampireFlightsDTOCollection extends BaseTypedCollection
{

    protected function getTypedClassName(): string
    {
        return VampireFlightDTO::class;
    }
}
