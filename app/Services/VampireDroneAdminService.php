<?php

namespace App\Services;

use App\Models\VampireDrone;
use App\Repositories\Contracts\VampireDroneRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class VampireDroneAdminService
{
    public function __construct(private readonly VampireDroneRepositoryInterface $repository)
    {
    }

    /**
     * @return Collection<VampireDrone>
     */
    public function getAllDrones(): Collection
    {
        return $this->repository->all();
    }

    public function getDroneById(int $id): VampireDrone
    {
        $drone = $this->repository->find($id);

        if (!$drone) {
            throw new ModelNotFoundException("Vampire Drone with ID {$id} not found");
        }

        return $drone;
    }

    /**
     * @param int $positionId
     * @return Collection<VampireDrone>
     */
    public function getDronesByPosition(int $positionId): Collection
    {
        return $this->repository->getByPosition($positionId);
    }

    public function createDrone(array $data): VampireDrone
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
