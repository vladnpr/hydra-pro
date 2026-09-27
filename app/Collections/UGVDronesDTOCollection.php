<?php

namespace App\Collections;

use App\DTOs\UGVDroneDTO;

class UGVDronesDTOCollection extends BaseTypedCollection
{

    protected function getTypedClassName(): string
    {
        return UGVDroneDTO::class;
    }
}
