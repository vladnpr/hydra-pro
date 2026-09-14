<?php

namespace App\Collections;

use App\DTOs\FPVDronesRemainingDTO;
use App\DTOs\ReconDronesRemainingDTO;
use Illuminate\Support\Collection;

class ReconDronesRemainingDTOCollection extends Collection
{
    public function __construct($items = [])
    {
        foreach ($items as $item) {
            throw_if(!($item instanceof ReconDronesRemainingDTO), 'Item is not an instance of ReconDronesRemainingDTO');
        }

        parent::__construct($items);
    }
}
