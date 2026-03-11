<?php

namespace App\DTOs;

use App\Enums\PositionTypesEnum;
use App\Http\Requests\AmmunitionUpdateRequest;

class UpdateAmmunitionDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly bool $status,
    ) {}

    public static function fromRequest(AmmunitionUpdateRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            type: $request->validated('type', PositionTypesEnum::FPV->value),
            status: (bool) $request->validated('status'),
        );
    }
}
