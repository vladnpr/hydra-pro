<?php

namespace App\Repositories\Eloquent;

use App\Models\VampireDrone;
use App\Repositories\Contracts\VampireDroneRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentVampireDroneRepository implements VampireDroneRepositoryInterface
{
    public function all(): Collection
    {
        return VampireDrone::with('position')->get();
    }

    public function getActive(): Collection
    {
        return VampireDrone::where('status', 'active')->with('position')->get();
    }

    public function getByPosition(int $positionId): Collection
    {
        return VampireDrone::where('position_id', $positionId)->get();
    }

    public function create(array $data): VampireDrone
    {
        return VampireDrone::create($data);
    }

    public function find(int $id): ?VampireDrone
    {
        return VampireDrone::with('position')->find($id);
    }

    public function update(int $id, array $data): bool
    {
        $drone = VampireDrone::find($id);
        if (!$drone) {
            return false;
        }
        return $drone->update($data);
    }

    public function delete(int $id): bool
    {
        $drone = VampireDrone::find($id);
        if (!$drone) {
            return false;
        }
        return (bool) $drone->delete();
    }
}
