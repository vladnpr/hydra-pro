<?php

namespace App\Repositories\Eloquent;

use App\Models\Drone;
use App\Repositories\Contracts\DroneRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentDroneRepository implements DroneRepositoryInterface
{
    public function all(): Collection
    {
        return Drone::all();
    }

    public function create(array $data): Drone
    {
        return Drone::create($data);
    }

    public function find(int $id): ?Drone
    {
        return Drone::find($id);
    }

    public function update(int $id, array $data): bool
    {
        $drone = $this->find($id);
        if (!$drone) {
            return false;
        }
        return $drone->update($data);
    }

    public function delete(int $id): bool
    {
        $drone = $this->find($id);
        if (!$drone) {
            return false;
        }
        return (bool) $drone->delete();
    }
}
