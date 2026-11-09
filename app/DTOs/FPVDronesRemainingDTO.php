<?php

namespace App\DTOs;

readonly class FPVDronesRemainingDTO
{
    public function __construct(
        private int    $id,
        private string $name,
        private string $model,
        private int    $quantity
    )
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }
}
