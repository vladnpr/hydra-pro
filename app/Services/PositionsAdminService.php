<?php

namespace App\Services;

use App\DTOs\PositionDTO;
use App\DTOs\CreatePositionDTO;
use App\DTOs\UpdatePositionDTO;
use App\Repositories\Contracts\PositionRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PositionsAdminService
{
    public function __construct(private PositionRepositoryInterface $repository)
    {
    }

    /**
     * @return Collection<PositionDTO>
     */
    public function getAllPositions(): Collection
    {
        return $this->repository->getActive()->map(fn($position) => PositionDTO::fromModel($position));
    }

    public function getPositionById(int $id): PositionDTO
    {
        $position = $this->repository->find($id);

        if (!$position) {
            throw new ModelNotFoundException("Position with ID {$id} not found");
        }

        return PositionDTO::fromModel($position);
    }

    public function createPosition(CreatePositionDTO $dto): PositionDTO
    {
        $position = $this->repository->create([
            'name' => $dto->name,
            'description' => $dto->description,
            'status' => $dto->status,
        ]);

        return PositionDTO::fromModel($position);
    }

    public function updatePosition(int $id, UpdatePositionDTO $dto): PositionDTO
    {
        $this->repository->update($id, [
            'name' => $dto->name,
            'description' => $dto->description,
            'status' => $dto->status,
        ]);

        return $this->getPositionById($id);
    }

    public function deletePosition(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
