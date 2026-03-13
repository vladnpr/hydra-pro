<?php

namespace App\Repositories\Contracts;

use App\Models\ReconDrone;
use Illuminate\Support\Collection;

interface ReconDroneRepositoryInterface
{
    /**
     * @return Collection<ReconDrone>
     */
    public function all(): Collection;

    /**
     * @return Collection<ReconDrone>
     */
    public function getActive(): Collection;

    /**
     * @param int $positionId
     * @return Collection<ReconDrone>
     */
    public function getByPosition(int $positionId): Collection;

    public function create(array $data): ReconDrone;

    public function find(int $id): ?ReconDrone;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;
}
