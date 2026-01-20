<?php

namespace App\DTOs;

use App\Models\CombatShift;

class CombatShiftDTO
{
    public function __construct(
        public readonly int $id,
        public readonly int $position_id,
        public readonly string $position_name,
        public readonly string $status,
        public readonly string $status_label,
        public readonly string $status_color,
        public readonly string $started_at,
        public readonly ?string $ended_at,
        public readonly array $drones,
        public readonly array $ammunition,
        public readonly array $crew,
    ) {}

    public static function fromModel(CombatShift $shift): self
    {
        return new self(
            id: $shift->id,
            position_id: $shift->position_id,
            position_name: $shift->position->name,
            status: $shift->status,
            status_label: $shift->status_label,
            status_color: $shift->status_color,
            started_at: $shift->started_at->format('Y-m-d H:i:s'),
            ended_at: $shift->ended_at?->format('Y-m-d H:i:s'),
            drones: $shift->drones->map(fn($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'quantity' => $d->pivot->quantity
            ])->toArray(),
            ammunition: $shift->ammunition->map(fn($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'quantity' => $a->pivot->quantity
            ])->toArray(),
            crew: $shift->crew->map(fn($c) => [
                'callsign' => $c->callsign,
                'role' => $c->role
            ])->toArray(),
        );
    }
}
