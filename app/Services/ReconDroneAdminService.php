<?php

namespace App\Services;

use App\Models\ReconDrone;
use App\Repositories\Contracts\ReconDroneRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ReconDroneAdminService
{
    public function __construct(private readonly ReconDroneRepositoryInterface $repository)
    {
    }

    /**
     * @return Collection<ReconDrone>
     */
    public function getAllDrones(): Collection
    {
        return $this->repository->all();
    }

    public function getDroneById(int $id): ReconDrone
    {
        $drone = $this->repository->find($id);

        if (!$drone) {
            throw new ModelNotFoundException("Recon Drone with ID {$id} not found");
        }

        return $drone;
    }

    public function createDrone(array $data): ReconDrone
    {
        return $this->repository->create($data);
    }

    public function updateDrone(int $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function deleteDrone(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
