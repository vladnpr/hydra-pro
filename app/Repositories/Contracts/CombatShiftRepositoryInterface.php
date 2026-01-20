<?php

namespace App\Repositories\Contracts;

use App\Models\CombatShift;
use Illuminate\Support\Collection;

interface CombatShiftRepositoryInterface
{
    public function all(): Collection;
    public function create(array $data): CombatShift;
    public function find(int $id): ?CombatShift;
    public function findActiveByUserId(int $userId): ?CombatShift;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function syncUsers(CombatShift $shift, array $userIds): void;
    public function attachUser(CombatShift $shift, int $userId): void;
    public function detachUser(CombatShift $shift, int $userId): void;
    public function syncDrones(CombatShift $shift, array $drones): void;
    public function syncAmmunition(CombatShift $shift, array $ammunition): void;
    public function syncCrew(CombatShift $shift, array $crew): void;
    public function syncFlights(CombatShift $shift, array $flights): void;
}
