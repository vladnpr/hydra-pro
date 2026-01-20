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
}
