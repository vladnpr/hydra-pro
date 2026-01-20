<?php

namespace App\DTOs;

class DroneDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $model,
        public readonly string $status,
        public readonly string $status_color,
    ) {}

    public static function fromModel(\App\Models\Drone $drone): self
    {
        return new self(
            id: $drone->id,
            name: $drone->name,
            model: $drone->model,
            status: $drone->status,
            status_color: $drone->status_color,
        );
    }
}
