<?php

namespace App\Repositories\Contracts;

use App\Models\Position;
use Illuminate\Support\Collection;

interface PositionRepositoryInterface
{
    /**
     * @return Collection<Position>
     */
    public function all(): Collection;

    /**
     * @param string|null $type
     * @return Collection<Position>
     */
    public function getActive(?string $type = null): Collection;

    public function create(array $data): Position;

    public function find(int $id): ?Position;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;
}
