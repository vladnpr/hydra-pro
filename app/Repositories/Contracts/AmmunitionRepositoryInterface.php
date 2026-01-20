<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;
use App\Models\Ammunition;

interface AmmunitionRepositoryInterface
{
    /**
     * @return Collection<Ammunition>
     */
    public function all(): Collection;

    /**
     * @return Collection<Ammunition>
     */
    public function getActive(): Collection;

    public function create(array $data): Ammunition;

    public function find(int $id): ?Ammunition;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;
}
