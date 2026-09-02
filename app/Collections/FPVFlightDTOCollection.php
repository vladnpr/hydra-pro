<?php

namespace App\Collections;

use App\DTOs\FPVDronesRemainingDTO;

class FPVFlightDTOCollection extends \Illuminate\Support\Collection
{
    /**
     * @param array $items
     * @throws \Throwable
     */
    public function __construct(array $items = [])
    {
        foreach ($items as $item) {
            throw_if(!($item instanceof FPVDronesRemainingDTO), 'Item is not an instance of FPVFlightDTO');
        }
        parent::__construct($items);
    }
}
