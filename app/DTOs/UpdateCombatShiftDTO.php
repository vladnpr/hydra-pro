<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class UpdateCombatShiftDTO
{
    public function __construct(
        public readonly array $user_ids,
        public readonly int $position_id,
        public readonly string $status,
        public readonly string $started_at,
        public readonly ?string $ended_at,
        public readonly array $drones,
        public readonly array $ammunition,
        public readonly array $crew,
        public readonly array $flights,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            user_ids: $request->validated('user_ids', []),
            position_id: $request->validated('position_id'),
            status: $request->validated('status'),
            started_at: $request->validated('started_at'),
            ended_at: $request->validated('ended_at'),
            drones: $request->validated('drones', []),
            ammunition: $request->validated('ammunition', []),
            crew: $request->validated('crew', []),
            flights: $request->validated('flights', []),
        );
    }
}
