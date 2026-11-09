<?php

namespace App\DTOs;

readonly class ADDronesRemainingDTO
{
    public function __construct(
        private int $droneId,
        private string $droneName,
        private string $model,
        private string $droneStatus,
        private int $quantity,
    )
    {
    }

    public function getDroneId(): int
    {
        return $this->droneId;
    }

    public function getDroneName(): string
    {
        return $this->droneName;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getDroneStatus(): string
    {
        return $this->droneStatus;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }
}
