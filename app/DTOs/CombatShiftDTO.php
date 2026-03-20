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
        public readonly array $recon_flights = [],
        public readonly array $vampire_drones = [],
        public readonly array $vampire_flights = [],
        public readonly array $vampire_flight_plans = [],
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
                    'shift_type' => $d->shift_type?->value ?? 'day',
                ])->toArray();
        }

        $vampireDrones = [];
        if ($shift->position && $shift->position->type === \App\Enums\PositionTypesEnum::VAMPIRE->value) {
            $vampireDrones = \App\Models\VampireDrone::where('position_id', $shift->position_id)
                ->get()
                ->map(fn($d) => [
                    'id' => $d->id,
                    'name' => $d->name,
                    'serial_number' => $d->serial_number,
                    'status' => $d->status,
                    'status_color' => $d->status_color,
                    'shift_type' => $d->shift_type?->value ?? 'day',
                ])->toArray();
        }

        $reconFlights = $shift->reconFlights->sortByDesc('flight_time')->groupBy(function($f) {
            $time = $f->flight_time;
            // Якщо це нічна зміна і час між 00:00 та 08:00, відносимо до попереднього дня
            if ($f->shift_type?->value === 'night' && $time->hour < 8) {
                return $time->copy()->subDay()->format('Y-m-d');
            }
            return $time->format('Y-m-d');
        })->map(fn($dayFlights) => $dayFlights->map(fn($f) => [
            'id' => $f->id,
            'drone_id' => $f->recon_drone_id,
            'drone_name' => $f->drone?->name,
            'ammunition' => $f->ammunition->map(fn($a) => ['name' => $a->name, 'quantity' => $a->pivot->quantity])->toArray(),
            'mission_type' => $f->mission_type->value,
            'mission_type_label' => $f->mission_type->value === 'recon' ? 'розвідка' : ($f->mission_type->value === 'combat' ? 'бойова (скид)' : ($f->mission_type->value === 'delivery' ? 'доставка' : $f->mission_type->value)),
            'coordinates' => $f->mission_type->value === 'delivery' ? $f->target_name : $f->coordinates,
            'target_name' => $f->target_name,
            'flight_time' => $f->flight_time->format('Y-m-d H:i:s'),
            'landing_time' => $f->landing_time?->format('Y-m-d H:i:s'),
            'result' => $f->result->value,
            'result_label' => $f->result->value === 'success' ? 'відпрацювали' : ($f->result->value === 'board_loosed' ? 'втрата борту' : $f->result->value),
            'shift_type' => $f->shift_type->value,
            'stream_status' => $f->stream_status,
            'description' => $f->description,
            'video_path' => $f->video_path,
        ]))->toArray();

        $vampireFlights = \App\Models\VampireFlight::where('combat_shift_id', $shift->id)
            ->with(['drone', 'flightPlan', 'ammunition'])
            ->orderByDesc('start_time')
            ->get()
            ->groupBy(function($f) {
                $time = $f->start_time;
                // Якщо час між 00:00 та 08:00, відносимо до попереднього дня
                if ($time->hour < 8) {
                    return $time->copy()->subDay()->format('Y-m-d');
                }
                return $time->format('Y-m-d');
            })
            ->map(fn($dayFlights) => $dayFlights->map(fn($f) => [
                'id' => $f->id,
                'drone_id' => $f->vampire_drone_id,
                'drone_name' => $f->drone?->name,
                'drone_serial' => $f->drone?->serial_number,
                'ammunition' => $f->ammunition->map(fn($a) => ['name' => $a->name, 'quantity' => $a->pivot->quantity])->toArray(),
                'mission_type' => $f->mission_type,
                'mission_type_label' => $f->mission_type === 'combat' ? 'бойова (мінування, бімба)' : ($f->mission_type === 'logistics' ? 'логістика' : $f->mission_type),
                'coordinates' => $f->coordinates ?: ($f->flightPlan?->coordinates ?? '-'),
                'position_name' => $f->flightPlan?->position_name ?? '-',
                'start_time' => $f->start_time->format('Y-m-d H:i:s'),
                'end_time' => $f->end_time?->format('Y-m-d H:i:s'),
                'result' => $f->result,
                'result_label' => match($f->result) {
                    'worked' => 'відпрацювали',
                    'loss' => 'втрата борту',
                    'not_worked' => 'не відпрацювали',
                    default => $f->result,
                },
                'stream_status' => $f->stream_status,
                'comment' => $f->comment,
                'video_path' => $f->video_path,
            ]))->toArray();

        $vampireFlightPlans = \App\Models\VampireFlightPlan::where('combat_shift_id', $shift->id)
            ->orderBy('order')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'position_name' => $p->position_name,
                'coordinates' => $p->coordinates,
                'status' => $p->status,
                'order' => $p->order,
            ])->toArray();

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
                'role' => $c->role,
                'shift_type' => $c->shift_type?->value ?? 'day'
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
            recon_flights: $reconFlights,
            vampire_drones: $vampireDrones,
            vampire_flights: $vampireFlights,
            vampire_flight_plans: $vampireFlightPlans,
        );
    }
}
