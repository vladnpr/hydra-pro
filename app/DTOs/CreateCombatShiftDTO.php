<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class CreateCombatShiftDTO
{
    public function __construct(
        public readonly array $user_ids,
        public readonly int $position_id,
        public readonly string $status,
        public readonly string $started_at,
        public readonly ?string $ended_at,
        public readonly array $drones, // [id => quantity, ...]
        public readonly array $ammunition, // [id => quantity, ...]
        public readonly array $crew, // [['callsign' => '...', 'role' => '...'], ...]
        public readonly array $flights,
        public readonly array $damaged_drones,
        public readonly array $damaged_coils,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            user_ids: $request->validated('user_ids', []),
            position_id: $request->validated('position_id'),
            status: $request->validated('status', 'opened'),
            started_at: $request->validated('started_at', now()->format('Y-m-d H:i:s')),
            ended_at: $request->validated('ended_at'),
            drones: $request->validated('drones', []),
            ammunition: $request->validated('ammunition', []),
            crew: $request->validated('crew', []),
            flights: $request->validated('flights', []),
            damaged_drones: $request->validated('damaged_drones', []),
            damaged_coils: $request->validated('damaged_coils', []),
        );
    }
}
