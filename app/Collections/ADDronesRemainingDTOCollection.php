<?php

namespace App\Collections;

use App\DTOs\ADDronesRemainingDTO;

class ADDronesRemainingDTOCollection extends BaseTypedCollection
{

    protected function getTypedClassNames(): array
    {
        return [
            ADDronesRemainingDTO::class
        ];
    }
}
