<?php

namespace App\Repositories\Eloquent;

use App\Models\CombatShift;
use App\Repositories\Contracts\CombatShiftRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentCombatShiftRepository implements CombatShiftRepositoryInterface
{
    public function all(): Collection
    {
        return CombatShift::with(['position', 'drones', 'ammunition', 'crew', 'flights.drone', 'flights.ammunition'])->latest()->get();
    }

    public function create(array $data): CombatShift
    {
        return CombatShift::create($data);
    }

    public function find(int $id): ?CombatShift
    {
        return CombatShift::with(['position', 'drones', 'ammunition', 'crew', 'flights.drone', 'flights.ammunition'])->find($id);
    }

    public function update(int $id, array $data): bool
    {
        $shift = CombatShift::find($id);
        if (!$shift) return false;
        return $shift->update($data);
    }

    public function delete(int $id): bool
    {
        $shift = CombatShift::find($id);
        if (!$shift) return false;
        return (bool) $shift->delete();
    }

    public function syncDrones(CombatShift $shift, array $drones): void
    {
        // $drones format expected: [id => ['quantity' => Q], ...]
        $shift->drones()->sync($drones);
    }

    public function syncAmmunition(CombatShift $shift, array $ammunition): void
    {
        $shift->ammunition()->sync($ammunition);
    }

    public function syncCrew(CombatShift $shift, array $crew): void
    {
        $shift->crew()->delete();
        if (!empty($crew)) {
            $shift->crew()->createMany($crew);
        }
    }

    public function syncFlights(CombatShift $shift, array $flights): void
    {
        $shift->flights()->delete();
        if (!empty($flights)) {
            $shift->flights()->createMany($flights);
        }
    }
}
