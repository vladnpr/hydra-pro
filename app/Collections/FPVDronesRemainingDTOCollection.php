<?php

namespace App\Collections;

use App\DTOs\FPVDronesRemainingDTO;

class FPVDronesRemainingDTOCollection extends \Illuminate\Support\Collection
{
    /**
     * @param $items
     * @throws \Throwable
     */
    public function __construct($items = [])
    {
        foreach ($items as $item) {
            throw_if(!($item instanceof FPVDronesRemainingDTO), 'Item is not an instance of FPVDronesRemainingDTO');
        }
        parent::__construct($items);
    }
}
