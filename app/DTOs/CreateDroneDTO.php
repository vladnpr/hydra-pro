<?php

namespace App\DTOs;

use App\Http\Requests\DroneStoreRequest;

class CreateDroneDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $model,
        public readonly bool $status = true,
    ) {}

    public static function fromRequest(DroneStoreRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            model: $request->validated('model'),
            status: $request->validated('status', 1),
        );
    }
}
