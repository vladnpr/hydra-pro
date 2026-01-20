<?php

namespace App\Repositories\Eloquent;

use App\Models\Ammunition;
use App\Repositories\Contracts\AmmunitionRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentAmmunitionRepository implements AmmunitionRepositoryInterface
{
    public function all(): Collection
    {
        return Ammunition::all();
    }

    public function getActive(): Collection
    {
        return Ammunition::where('status', true)->get();
    }

    public function create(array $data): Ammunition
    {
        return Ammunition::create($data);
    }

    public function find(int $id): ?Ammunition
    {
        return Ammunition::find($id);
    }

    public function update(int $id, array $data): bool
    {
        $ammunition = $this->find($id);
        if (!$ammunition) {
            return false;
        }
        return $ammunition->update($data);
    }

    public function delete(int $id): bool
    {
        $ammunition = $this->find($id);
        if (!$ammunition) {
            return false;
        }
        return (bool) $ammunition->delete();
    }
}
