<?php

namespace App\DTOs;

use App\Http\Requests\PositionUpdateRequest;

class UpdatePositionDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description,
        public readonly string $type,
        public readonly bool $status,
    ) {}

    public static function fromRequest(PositionUpdateRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            description: $request->validated('description'),
            type: $request->validated('type'),
            status: (bool) $request->validated('status'),
        );
    }
}
