<?php

namespace App\Services;

use App\DTOs\AmmunitionDTO;
use App\DTOs\CreateAmmunitionDTO;
use App\DTOs\UpdateAmmunitionDTO;
use App\Repositories\Contracts\AmmunitionRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AmmunitionAdminService
{
    public function __construct(private readonly AmmunitionRepositoryInterface $repository)
    {
    }

    /**
     * @return Collection<AmmunitionDTO>
     */
    public function getAllAmmunition(): Collection
    {
        return $this->repository->getActive()->map(fn($item) => AmmunitionDTO::fromModel($item));
    }

    public function getAmmunitionById(int $id): AmmunitionDTO
    {
        $item = $this->repository->find($id);

        if (!$item) {
            throw new ModelNotFoundException("Ammunition with ID {$id} not found");
        }

        return AmmunitionDTO::fromModel($item);
    }

    public function createAmmunition(CreateAmmunitionDTO $dto): AmmunitionDTO
    {
        $item = $this->repository->create([
            'name' => $dto->name,
            'status' => $dto->status,
        ]);

        return AmmunitionDTO::fromModel($item);
    }

    public function updateAmmunition(int $id, UpdateAmmunitionDTO $dto): AmmunitionDTO
    {
        $this->repository->update($id, [
            'name' => $dto->name,
            'status' => $dto->status,
        ]);

        return $this->getAmmunitionById($id);
    }

    public function deleteAmmunition(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
