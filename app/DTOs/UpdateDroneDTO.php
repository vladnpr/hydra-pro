<?php

namespace App\DTOs;

use App\Http\Requests\DroneUpdateRequest;

class UpdateDroneDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $model,
        public readonly bool $status,
    ) {}

    public static function fromRequest(DroneUpdateRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            model: $request->validated('model'),
            status: $request->validated('status'),
        );
    }
}
