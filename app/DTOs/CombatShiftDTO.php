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
        public readonly string $type,
        public readonly string $status,
        public readonly string $status_label,
        public readonly string $status_color,
        public readonly string $started_at,
        public readonly ?string $ended_at,
        public readonly array $drones,
        public readonly array $ammunition,
        public readonly array $crew,
        public readonly array $flights,
        public readonly array $damaged_drones,
        public readonly array $damaged_coils,
        public readonly array $recon_drones = [],
    ) {}

    public static function fromModel(CombatShift $shift): self
    {
        $reconDrones = [];
        if ($shift->position && $shift->position->type === \App\Enums\PositionTypesEnum::RECON->value) {
            $reconDrones = \App\Models\ReconDrone::where('position_id', $shift->position_id)
                ->get()
                ->map(fn($d) => [
                    'id' => $d->id,
                    'name' => $d->name,
                    'serial_number' => $d->serial_number,
                    'status' => $d->status,
                    'status_color' => $d->status_color,
                ])->toArray();
        }

        return new self(
            id: $shift->id,
            users: $shift->users->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
            ])->toArray(),
            position_id: $shift->position_id,
            position_name: $shift->position->name,
            type: $shift->type->value ?? 'fpv',
            status: $shift->status,
            status_label: $shift->status_label,
            status_color: $shift->status_color,
            started_at: $shift->started_at->format('Y-m-d H:i:s'),
            ended_at: $shift->ended_at?->format('Y-m-d H:i:s'),
            drones: $shift->drones->map(function($d) {
                return [
                    'id' => $d->id,
                    'name' => $d->name,
                    'model' => $d->model,
                    'quantity' => $d->pivot->quantity
                ];
            })->toArray(),
            ammunition: $shift->ammunition->map(function($a) {
                return [
                    'id' => $a->id,
                    'name' => $a->name,
                    'quantity' => $a->pivot->quantity
                ];
            })->toArray(),
            crew: $shift->crew->map(fn($c) => [
                'callsign' => $c->callsign,
                'role' => $c->role
            ])->toArray(),
            flights: $shift->flights->sortByDesc('flight_time')->groupBy(fn($f) => $f->flight_time->format('Y-m-d'))->map(fn($dayFlights) => $dayFlights->map(fn($f) => [
                'id' => $f->id,
                'drone_id' => $f->drone_id,
                'drone_name' => $f->drone?->name,
                'drone_model' => $f->drone?->model,
                'ammunition_id' => $f->ammunition_id,
                'ammunition_name' => $f->ammunition->name,
                'coordinates' => $f->coordinates,
                'flight_time' => $f->flight_time->format('Y-m-d H:i:s'),
                'result' => $f->result,
                'detonation' => $f->detonation,
                'stream' => $f->stream,
                'note' => $f->note,
                'video_path' => $f->video_path,
            ]))->toArray(),
            damaged_drones: $shift->damaged_drones ?? [],
            damaged_coils: $shift->damaged_coils ?? [],
            recon_drones: $reconDrones,
        );
    }
}
