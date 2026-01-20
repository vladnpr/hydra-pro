<?php

namespace App\DTOs;

use App\Http\Requests\AmmunitionUpdateRequest;

class UpdateAmmunitionDTO
{
    public function __construct(
        public readonly string $name,
        public readonly bool $status,
    ) {}

    public static function fromRequest(AmmunitionUpdateRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            status: (bool) $request->validated('status'),
        );
    }
}
