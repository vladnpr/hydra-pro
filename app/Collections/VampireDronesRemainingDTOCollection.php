<?php

namespace App\Collections;

use App\DTOs\VampireDronesRemainingDTO;

class VampireDronesRemainingDTOCollection extends BaseTypedCollection
{

    protected function getTypedClassNames(): array
    {
        return [
            VampireDronesRemainingDTO::class
        ];
    }
}
