<?php

namespace App\Repositories\Eloquent;

use App\Models\UgvDrone;
use App\Repositories\Contracts\UgvDroneRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentUgvDroneRepository implements UgvDroneRepositoryInterface
{
    public function all(): Collection
    {
        return UgvDrone::with('position')->get();
    }

    public function getActive(): Collection
    {
        return UgvDrone::where('status', 'active')->with('position')->get();
    }

    public function getByPosition(int $positionId): Collection
    {
        return UgvDrone::where('position_id', $positionId)->get();
    }

    public function create(array $data): UgvDrone
    {
        return UgvDrone::create($data);
    }

    public function find(int $id): ?UgvDrone
    {
        return UgvDrone::with('position')->find($id);
    }

    public function update(int $id, array $data): bool
    {
        $drone = UgvDrone::find($id);
        if (!$drone) {
            return false;
        }
        return $drone->update($data);
    }

    public function delete(int $id): bool
    {
        $drone = UgvDrone::find($id);
        if (!$drone) {
            return false;
        }
        return (bool) $drone->delete();
    }
}
