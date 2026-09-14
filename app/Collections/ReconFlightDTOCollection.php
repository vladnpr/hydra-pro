<?php

namespace App\Collections;

use App\DTOs\ReconFlightDTO;
use Illuminate\Support\Collection;

class ReconFlightDTOCollection extends Collection
{
    public function __construct($items = [])
    {
        foreach ($items as $item) {
            throw_if(!($item instanceof ReconFlightDTO), 'Item is not an instance of ReconFlightDTO');
        }
        parent::__construct($items);
    }
}
