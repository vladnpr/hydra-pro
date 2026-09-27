<?php

namespace App\Collections;

use App\DTOs\UGVRaceDTO;

class UGVRacesDTOCollection extends BaseTypedCollection
{

    protected function getTypedClassName(): string
    {
        return UGVRaceDTO::class;
    }
}
