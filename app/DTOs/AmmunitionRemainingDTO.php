<?php

namespace App\DTOs;

use App\Enums\PositionTypesEnum;

class AmmunitionRemainingDTO
{
    public function __construct(
        public string $name,
        public int $quantity,
        public int $status,
        public PositionTypesEnum $shift_type,
    )
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getShiftType(): PositionTypesEnum
    {
        return $this->shift_type;
    }
}
