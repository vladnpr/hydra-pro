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
    public function __construct(
        private CombatShiftRepositoryInterface $repository
    )
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

    /**
     * @return Collection<CombatShiftDTO>
     */
    public function getActiveShifts(): Collection
    {
        return $this->repository->getActiveShifts()->map(fn($shift) => CombatShiftDTO::fromModel($shift));
    }

    public function createShift(CreateCombatShiftDTO $dto): CombatShiftDTO
    {
        return DB::transaction(function () use ($dto) {
            $shiftModel = $this->repository->create([
                'position_id' => $dto->position_id,
                'status' => $dto->status,
                'started_at' => $dto->started_at,
                'ended_at' => $dto->ended_at,
                'damaged_drones' => $dto->damaged_drones,
                'damaged_coils' => $dto->damaged_coils,
            ]);

            if (!empty($dto->user_ids)) {
                $this->repository->syncUsers($shiftModel, $dto->user_ids);
            } else {
                $this->repository->syncUsers($shiftModel, [Auth::id()]);
            }

            if (!empty($dto->crew)) {
                $this->repository->syncCrew($shiftModel, $dto->crew);
            }

            if (!empty($dto->flights)) {
                $this->repository->syncFlights($shiftModel, $dto->flights);
            }

            $shift = CombatShiftDTO::fromModel($shiftModel->load(['position', 'drones', 'ammunition', 'crew', 'flights']));

            if (!empty($dto->drones)) {
                $this->repository->syncDrones($shiftModel, $this->formatPivotData($shift, $dto->drones, 'drone'));
            }

            if (!empty($dto->ammunition)) {
                $this->repository->syncAmmunition($shiftModel, $this->formatPivotData($shift, $dto->ammunition, 'ammunition'));
            }

            return CombatShiftDTO::fromModel($shiftModel->load(['position', 'drones', 'ammunition', 'crew', 'flights']));
        });
    }

    public function updateShift(int $id, UpdateCombatShiftDTO $dto): CombatShiftDTO
    {
        return DB::transaction(function () use ($id, $dto) {
            $shiftModel = $this->repository->find($id);
            if (!$shiftModel) {
                throw new ModelNotFoundException("Combat shift with ID {$id} not found");
            }

            $updateData = [
                'position_id' => $dto->position_id,
                'status' => $dto->status,
                'started_at' => $dto->started_at,
                'ended_at' => $dto->ended_at,
                'damaged_drones' => $dto->damaged_drones,
                'damaged_coils' => $dto->damaged_coils,
            ];

            $this->repository->update($id, $updateData);

            if (!empty($dto->user_ids)) {
                $this->repository->syncUsers($shiftModel, $dto->user_ids);
            }

            $this->repository->syncCrew($shiftModel, $dto->crew);
            $this->repository->syncFlights($shiftModel, $dto->flights);

            $shift = CombatShiftDTO::fromModel($shiftModel->load(['position', 'drones', 'ammunition', 'crew', 'flights']));

            $this->repository->syncDrones($shiftModel, $this->formatPivotData($shift, $dto->drones, 'drone'));
            $this->repository->syncAmmunition($shiftModel, $this->formatPivotData($shift, $dto->ammunition, 'ammunition'));

            return CombatShiftDTO::fromModel($shiftModel->load(['position', 'drones', 'ammunition', 'crew', 'flights']));
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
        $totalFlights = \App\Models\CombatShiftFlight::all()->count();

        $totalHits = \App\Models\CombatShiftFlight::with('ammunition')->where([
            'result' => 'влучання',
        ])->whereHas('ammunition', function ($query) {
            $query->where('name', 'NOT IN', 'Інше');
        })->count();

        $totalAreaHits = \App\Models\CombatShiftFlight::with('ammunition')
            ->where('result', 'удар в районі цілі')
            ->count();

        $totalMisses = \App\Models\CombatShiftFlight::where('result', 'втрата борту')->count();

        $totalDetonations = \App\Models\CombatShiftFlight::where('detonation', 'так')->count();

        $totalNonDetonations = \App\Models\CombatShiftFlight::where('detonation', 'ні')->count();

        return [
            'total_flights' => $totalFlights,
            'total_hits' => $totalHits,
            'total_area_hits' => $totalAreaHits,
            'total_misses' => $totalMisses,
            'total_detonations' => $totalDetonations,
            'total_non_detonations' => $totalNonDetonations,
        ];
    }

    private function formatPivotData(CombatShiftDTO $shift, array $items, string $type): array
    {
        $formatted = [];
        foreach ($items as $id => $quantity) {
            if ($quantity >= 0) {
                // Determine how many were consumed
                $consumed = 0;
                if ($type === 'drone') {
                    $consumed = collect($shift->flights)->flatten(1)->where('drone_id', $id)->count();
                } else {
                    $consumed = collect($shift->flights)->flatten(1)->where('ammunition_id', $id)->count();
                }

                // $quantity from form is the 'Actual' (remaining) amount.
                // Database stores the 'initial' amount.
                // So initial = actual + consumed.
                $formatted[$id] = ['quantity' => $quantity + $consumed];
            }
        }
        return $formatted;
    }
}
