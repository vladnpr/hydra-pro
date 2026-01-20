<?php

namespace App\DTOs;

use App\Http\Requests\PositionStoreRequest;

class CreatePositionDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description,
        public readonly bool $status = true,
    ) {}

    public static function fromRequest(PositionStoreRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            description: $request->validated('description'),
            status: (bool) $request->validated('status', true),
        );
    }
}
