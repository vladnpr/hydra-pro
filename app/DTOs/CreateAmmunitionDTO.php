<?php

namespace App\DTOs;

use App\Http\Requests\AmmunitionStoreRequest;

class CreateAmmunitionDTO
{
    public function __construct(
        public readonly string $name,
        public readonly bool $status = true,
    ) {}

    public static function fromRequest(AmmunitionStoreRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            status: (bool) $request->validated('status', true),
        );
    }
}
