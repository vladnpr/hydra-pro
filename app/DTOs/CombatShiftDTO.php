<?php

namespace App\DTOs;

use App\Models\CombatShift;

class CombatShiftDTO
{
    public function __construct(
        public readonly int $id,
        public readonly array $users,
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
        public readonly array $flights,
    ) {}

    public static function fromModel(CombatShift $shift): self
    {
        return new self(
            id: $shift->id,
            users: $shift->users->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
            ])->toArray(),
            position_id: $shift->position_id,
            position_name: $shift->position->name,
            status: $shift->status,
            status_label: $shift->status_label,
            status_color: $shift->status_color,
            started_at: $shift->started_at->format('Y-m-d H:i:s'),
            ended_at: $shift->ended_at?->format('Y-m-d H:i:s'),
            drones: $shift->drones->map(function($d) use ($shift) {
                $consumed = $shift->flights->where('drone_id', $d->id)->count();
                return [
                    'id' => $d->id,
                    'name' => $d->name,
                    'model' => $d->model,
                    'quantity' => $d->pivot->quantity - $consumed
                ];
            })->toArray(),
            ammunition: $shift->ammunition->map(function($a) use ($shift) {
                $consumed = $shift->flights->where('ammunition_id', $a->id)->count();
                return [
                    'id' => $a->id,
                    'name' => $a->name,
                    'quantity' => $a->pivot->quantity - $consumed
                ];
            })->toArray(),
            crew: $shift->crew->map(fn($c) => [
                'callsign' => $c->callsign,
                'role' => $c->role
            ])->toArray(),
            flights: $shift->flights->sortByDesc('flight_time')->groupBy(fn($f) => $f->flight_time->format('Y-m-d'))->map(fn($dayFlights) => $dayFlights->map(fn($f) => [
                'id' => $f->id,
                'drone_id' => $f->drone_id,
                'drone_name' => $f->drone->name,
                'drone_model' => $f->drone->model,
                'ammunition_id' => $f->ammunition_id,
                'ammunition_name' => $f->ammunition->name,
                'coordinates' => $f->coordinates,
                'flight_time' => $f->flight_time->format('Y-m-d H:i:s'),
                'result' => $f->result,
                'detonation' => $f->detonation,
                'stream' => $f->stream,
                'note' => $f->note,
            ]))->toArray(),
        );
    }
}
