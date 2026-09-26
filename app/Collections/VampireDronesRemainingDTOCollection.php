<?php

namespace App\Collections;

use App\DTOs\VampireDronesRemainingDTO;

class VampireDronesRemainingDTOCollection extends BaseTypedCollection
{

    protected function getTypedClassName(): string
    {
        return VampireDronesRemainingDTO::class;
    }
}
