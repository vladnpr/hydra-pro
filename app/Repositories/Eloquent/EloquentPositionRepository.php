<?php

namespace App\Repositories\Eloquent;

use App\Models\Position;
use App\Repositories\Contracts\PositionRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentPositionRepository implements PositionRepositoryInterface
{
    public function all(): Collection
    {
        return Position::all();
    }

    public function create(array $data): Position
    {
        return Position::create($data);
    }

    public function find(int $id): ?Position
    {
        return Position::find($id);
    }

    public function update(int $id, array $data): bool
    {
        $position = $this->find($id);
        if (!$position) {
            return false;
        }
        return $position->update($data);
    }

    public function delete(int $id): bool
    {
        $position = $this->find($id);
        if (!$position) {
            return false;
        }
        return (bool) $position->delete();
    }
}
