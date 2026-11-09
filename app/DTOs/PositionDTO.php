<?php

namespace App\DTOs;

use App\Models\Position;

class PositionDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly string $type,
        public readonly int $team_number,
        public readonly bool $status,
        public readonly string $status_color,
    ) {}

    public static function fromModel(Position $position): self
    {
        return new self(
            id: $position->id,
            name: $position->name,
            description: $position->description,
            type: $position->type,
            team_number: $position->team_number,
            status: $position->status,
            status_color: $position->status_color,
        );
    }
}
