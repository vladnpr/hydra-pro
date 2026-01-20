<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class UpdateCombatShiftDTO
{
    public function __construct(
        public readonly int $position_id,
        public readonly string $status,
        public readonly string $started_at,
        public readonly ?string $ended_at,
        public readonly array $drones,
        public readonly array $ammunition,
        public readonly array $crew,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            position_id: $request->validated('position_id'),
            status: $request->validated('status'),
            started_at: $request->validated('started_at'),
            ended_at: $request->validated('ended_at'),
            drones: $request->validated('drones', []),
            ammunition: $request->validated('ammunition', []),
            crew: $request->validated('crew', []),
        );
    }
}
