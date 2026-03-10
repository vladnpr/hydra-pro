<?php

namespace App\DTOs;

readonly class SpendFPVDTO
{
    public function __construct(
        private readonly ?array $drones,
        private readonly ?array $ammunition,
    )
    {
    }

    public function getDrones(): ?array
    {
        return $this->drones;
    }

    public function getAmmunition(): ?array
    {
        return $this->ammunition;
    }
}
