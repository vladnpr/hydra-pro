<?php

namespace App\Repositories\Contracts;

use App\Models\VampireDrone;
use Illuminate\Support\Collection;

interface VampireDroneRepositoryInterface
{
    public function all(): Collection;

    public function getActive(): Collection;

    public function getByPosition(int $positionId): Collection;

    public function create(array $data): VampireDrone;

    public function find(int $id): ?VampireDrone;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;
}
