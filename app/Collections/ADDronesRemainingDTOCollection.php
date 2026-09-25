<?php

namespace App\Collections;

use App\DTOs\ADDronesRemainingDTO;

class ADDronesRemainingDTOCollection extends BaseTypedCollection
{

    protected function getTypedClassName(): string
    {
        return ADDronesRemainingDTO::class;
    }
}
