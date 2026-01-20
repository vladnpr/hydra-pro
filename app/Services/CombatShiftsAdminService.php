<?php

namespace App\Services;

use App\DTOs\CombatShiftDTO;
use App\DTOs\CreateCombatShiftDTO;
use App\DTOs\UpdateCombatShiftDTO;
use App\Repositories\Contracts\CombatShiftRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CombatShiftsAdminService
{
    public function __construct(private CombatShiftRepositoryInterface $repository)
    {
    }

    /**
     * @return Collection<CombatShiftDTO>
     */
    public function getAllShifts(): Collection
    {
        return $this->repository->all()->map(fn($shift) => CombatShiftDTO::fromModel($shift));
    }

    public function getShiftById(int $id): CombatShiftDTO
    {
        $shift = $this->repository->find($id);

        if (!$shift) {
            throw new ModelNotFoundException("Combat shift with ID {$id} not found");
        }

        return CombatShiftDTO::fromModel($shift);
    }

    public function getActiveShiftByUserId(int $userId): ?CombatShiftDTO
    {
        $shift = $this->repository->findActiveByUserId($userId);

        if (!$shift) {
            return null;
        }

        return CombatShiftDTO::fromModel($shift);
    }

    public function createShift(CreateCombatShiftDTO $dto): CombatShiftDTO
    {
        return DB::transaction(function () use ($dto) {
            $shift = $this->repository->create([
                'position_id' => $dto->position_id,
                'status' => $dto->status,
                'started_at' => $dto->started_at,
                'ended_at' => $dto->ended_at,
            ]);

            if (!empty($dto->user_ids)) {
                $this->repository->syncUsers($shift, $dto->user_ids);
            } else {
                // Default to current user if none provided
                $this->repository->syncUsers($shift, [Auth::id()]);
            }

            if (!empty($dto->drones)) {
                $this->repository->syncDrones($shift, $this->formatPivotData($dto->drones));
            }

            if (!empty($dto->ammunition)) {
                $this->repository->syncAmmunition($shift, $this->formatPivotData($dto->ammunition));
            }

            if (!empty($dto->crew)) {
                $this->repository->syncCrew($shift, $dto->crew);
            }

            if (!empty($dto->flights)) {
                $this->repository->syncFlights($shift, $dto->flights);
            }

            return CombatShiftDTO::fromModel($shift->load(['position', 'drones', 'ammunition', 'crew', 'flights']));
        });
    }

    public function updateShift(int $id, UpdateCombatShiftDTO $dto): CombatShiftDTO
    {
        return DB::transaction(function () use ($id, $dto) {
            $shift = $this->repository->find($id);
            if (!$shift) {
                throw new ModelNotFoundException("Combat shift with ID {$id} not found");
            }

            $updateData = [
                'position_id' => $dto->position_id,
                'status' => $dto->status,
                'started_at' => $dto->started_at,
                'ended_at' => $dto->ended_at,
            ];

            $this->repository->update($id, $updateData);

            if (!empty($dto->user_ids)) {
                $this->repository->syncUsers($shift, $dto->user_ids);
            }

            $this->repository->syncDrones($shift, $this->formatPivotData($dto->drones));
            $this->repository->syncAmmunition($shift, $this->formatPivotData($dto->ammunition));
            $this->repository->syncCrew($shift, $dto->crew);
            $this->repository->syncFlights($shift, $dto->flights);

            return CombatShiftDTO::fromModel($shift->load(['position', 'drones', 'ammunition', 'crew', 'flights']));
        });
    }

    public function deleteShift(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function joinShift(int $shiftId, int $userId): void
    {
        $shift = $this->repository->find($shiftId);
        if ($shift && $shift->status === 'opened') {
            $this->repository->attachUser($shift, $userId);
        }
    }

    public function leaveShift(int $shiftId, int $userId): void
    {
        $shift = $this->repository->find($shiftId);
        if ($shift) {
            $this->repository->detachUser($shift, $userId);
        }
    }

    public function finishShift(int $shiftId): void
    {
        $shift = $this->repository->find($shiftId);
        if ($shift && $shift->status === 'opened') {
            $this->repository->update($shiftId, [
                'status' => 'closed',
                'ended_at' => now(),
            ]);
        }
    }

    public function reopenShift(int $shiftId): void
    {
        $shift = $this->repository->find($shiftId);
        if ($shift && $shift->status === 'closed') {
            $this->repository->update($shiftId, [
                'status' => 'opened',
                'ended_at' => null,
            ]);
        }
    }

    public function getGlobalStats(): array
    {
        $flights = \App\Models\CombatShiftFlight::all();

        return [
            'total_flights' => $flights->count(),
            'total_hits' => $flights->where('result', 'влучання')->count(),
            'total_area_hits' => $flights->where('result', 'удар в районі цілі')->count(),
            'total_misses' => $flights->where('result', 'недольот')->count(),
            'total_detonations' => $flights->where('detonation', 'так')->count(),
            'total_non_detonations' => $flights->where('detonation', 'ні')->count(),
        ];
    }

    private function formatPivotData(array $items): array
    {
        $formatted = [];
        foreach ($items as $id => $quantity) {
            if ($quantity > 0) {
                $formatted[$id] = ['quantity' => $quantity];
            }
        }
        return $formatted;
    }
}
