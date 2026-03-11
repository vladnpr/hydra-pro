<?php

namespace App\Repositories\Eloquent;

use App\Models\ReconDrone;
use App\Repositories\Contracts\ReconDroneRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentReconDroneRepository implements ReconDroneRepositoryInterface
{
    public function all(): Collection
    {
        return ReconDrone::with('position')->get();
    }

    public function getActive(): Collection
    {
        return ReconDrone::where('status', 'active')->with('position')->get();
    }

    public function create(array $data): ReconDrone
    {
        return ReconDrone::create($data);
    }

    public function find(int $id): ?ReconDrone
    {
        return ReconDrone::with('position')->find($id);
    }

    public function update(int $id, array $data): bool
    {
        $drone = ReconDrone::find($id);
        if (!$drone) {
            return false;
        }
        return $drone->update($data);
    }

    public function delete(int $id): bool
    {
        $drone = ReconDrone::find($id);
        if (!$drone) {
            return false;
        }
        return (bool) $drone->delete();
    }
}
