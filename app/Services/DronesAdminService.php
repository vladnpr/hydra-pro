<?php

namespace App\Services;

use App\DTOs\DroneDTO;
use App\DTOs\CreateDroneDTO;
use App\DTOs\UpdateDroneDTO;
use App\Repositories\Contracts\DroneRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DronesAdminService
{
    public function __construct(private DroneRepositoryInterface $repository)
    {
    }

    /**
     * @return Collection<DroneDTO>
     */
    public function getAllDrones(): Collection
    {
        return $this->repository->getActive()->map(fn($drone) => DroneDTO::fromModel($drone));
    }

    public function getDroneById(int $id): DroneDTO
    {
        $drone = $this->repository->find($id);

        if (!$drone) {
            throw new ModelNotFoundException("Drone with ID {$id} not found");
        }

        return DroneDTO::fromModel($drone);
    }

    public function createDrone(CreateDroneDTO $dto): DroneDTO
    {
        $drone = $this->repository->create([
            'name' => $dto->name,
            'model' => $dto->model,
            'status' => $dto->status,
        ]);

        return DroneDTO::fromModel($drone);
    }

    public function updateDrone(int $id, UpdateDroneDTO $dto): DroneDTO
    {
        $this->repository->update($id, [
            'name' => $dto->name,
            'model' => $dto->model,
            'status' => $dto->status,
        ]);

        return $this->getDroneById($id);
    }

    public function deleteDrone(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
