<?php

namespace App\Collections;

use App\DTOs\FPVDronesRemainingDTO;
use Illuminate\Support\Collection;

class ReconDronesRemainingDTOCollection extends Collection
{
    public function __construct($items = [])
    {
        foreach ($items as $item) {
            throw_if(!($item instanceof FPVDronesRemainingDTO), 'Item is not an instance of FPVFlightDTO');
        }

        parent::__construct($items);
    }
}
