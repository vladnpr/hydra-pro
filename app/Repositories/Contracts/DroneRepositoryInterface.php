<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface DroneRepositoryInterface
{
    /**
     * @return Collection<\App\Models\Drone>
     */
    public function all(): Collection;

    public function create(array $data): \App\Models\Drone;

    public function find(int $id): ?\App\Models\Drone;

    public function update(int $id, array $data): bool;
}
