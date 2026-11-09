<?php

namespace App\Collections;

use App\DTOs\UGVRaceDTO;

class UGVRacesDTOCollection extends BaseTypedCollection
{

    protected function getTypedClassNames(): array
    {
        return [
            UGVRaceDTO::class
        ];
    }
}
