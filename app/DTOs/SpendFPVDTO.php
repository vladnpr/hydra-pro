<?php

namespace App\DTOs;

use Illuminate\Support\Collection;

readonly class SpendFPVDTO
{
    public function __construct(
        private int $shiftId,
        private string $positionName,
        private ?Collection $drones,
        private ?Collection $ammunition,
    )
    {
    }

    public function getShiftId(): int
    {
        return $this->shiftId;
    }

    public function getPositionName(): string
    {
        return $this->positionName;
    }

    public function getDrones(): ?Collection
    {
        return $this->drones;
    }

    public function getAmmunition(): ?Collection
    {
        return $this->ammunition;
    }
}
