<?php

namespace App\Services;

use App\Models\UgvDrone;
use App\Repositories\Contracts\UgvDroneRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UgvDroneAdminService
{
    public function __construct(private readonly UgvDroneRepositoryInterface $repository)
    {
    }

    /**
     * @return Collection<UgvDrone>
     */
    public function getAllDrones(): Collection
    {
        return $this->repository->all();
    }

    public function getDroneById(int $id): UgvDrone
    {
        $drone = $this->repository->find($id);

        if (!$drone) {
            throw new ModelNotFoundException("UGV Drone with ID {$id} not found");
        }

        return $drone;
    }

    /**
     * @param int $positionId
     * @return Collection<UgvDrone>
     */
    public function getDronesByPosition(int $positionId): Collection
    {
        return $this->repository->getByPosition($positionId);
    }

    public function createDrone(array $data): UgvDrone
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
