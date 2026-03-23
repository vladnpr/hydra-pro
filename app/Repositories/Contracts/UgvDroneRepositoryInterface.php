<?php

namespace App\Repositories\Contracts;

use App\Models\UgvDrone;
use Illuminate\Support\Collection;

interface UgvDroneRepositoryInterface
{
    public function all(): Collection;

    public function getActive(): Collection;

    public function getByPosition(int $positionId): Collection;

    public function create(array $data): UgvDrone;

    public function find(int $id): ?UgvDrone;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;
}
