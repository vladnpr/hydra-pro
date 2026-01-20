<?php

namespace App\Services;

use App\DTOs\DroneDTO;
use App\DTOs\CreateDroneDTO;
use App\Repositories\Contracts\DroneRepositoryInterface;
use Illuminate\Support\Collection;

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
        return $this->repository->all()->map(fn($drone) => DroneDTO::fromModel($drone));
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
}
