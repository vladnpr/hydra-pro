<?php

namespace App\Collections;

use App\DTOs\UGVDroneDTO;

class UGVDronesDTOCollection extends BaseTypedCollection
{

    protected function getTypedClassNames(): array
    {
        return [
            UGVDroneDTO::class
        ];
    }
}
