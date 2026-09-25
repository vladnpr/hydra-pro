<?php

namespace App\Collections;

use App\DTOs\ADFlightsDTO;

class ADFlightsDTOCollection extends BaseTypedCollection
{
    protected function getTypedClassName(): string
    {
        return ADFlightsDTO::class;
    }
}
