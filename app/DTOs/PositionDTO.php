<?php

namespace App\DTOs;

use App\Models\Position;

class PositionDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly bool $status,
        public readonly string $status_color,
    ) {}

    public static function fromModel(Position $position): self
    {
        return new self(
            id: $position->id,
            name: $position->name,
            description: $position->description,
            status: $position->status,
            status_color: $position->status_color,
        );
    }
}
