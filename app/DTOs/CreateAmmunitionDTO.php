<?php

namespace App\DTOs;

use App\Enums\PositionTypesEnum;
use App\Http\Requests\AmmunitionStoreRequest;

class CreateAmmunitionDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $type = PositionTypesEnum::FPV->value,
        public readonly bool $status = true,
    ) {}

    public static function fromRequest(AmmunitionStoreRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            type: $request->validated('type', PositionTypesEnum::FPV->value),
            status: (bool) $request->validated('status', true),
        );
    }
}
