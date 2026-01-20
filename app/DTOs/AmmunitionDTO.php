<?php

namespace App\DTOs;

use App\Models\Ammunition;

class AmmunitionDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly bool $status,
        public readonly string $status_color,
    ) {}

    public static function fromModel(Ammunition $ammunition): self
    {
        return new self(
            id: $ammunition->id,
            name: $ammunition->name,
            status: (bool) $ammunition->status,
            status_color: $ammunition->status_color,
        );
    }
}
