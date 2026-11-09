<?php

namespace App\DTOs;

use App\Http\Requests\PositionStoreRequest;

class CreatePositionDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description,
        public readonly string $type,
        public readonly int $team_number,
        public readonly bool $status = true,
    ) {}

    public static function fromRequest(PositionStoreRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            description: $request->validated('description'),
            type: $request->validated('type'),
            team_number: $request->validated('team_number'),
            status: (bool) $request->validated('status', true),
        );
    }
}
