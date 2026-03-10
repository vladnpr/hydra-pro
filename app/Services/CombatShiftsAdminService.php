<?php

namespace App\Services;

use App\DTOs\CombatShiftDTO;
use App\DTOs\CreateCombatShiftDTO;
use App\DTOs\UpdateCombatShiftDTO;
use App\Repositories\CombatShiftFlightsRepository;
use App\Repositories\Contracts\CombatShiftRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

readonly class CombatShiftsAdminService
{
    public function __construct(
        readonly private CombatShiftRepositoryInterface $combatShiftRepository,
        readonly private CombatShiftFlightsRepository   $flightRepository,
    )
    {
    }

    /**
     * @return Collection<CombatShiftDTO>
     */
    public function getAllShifts(): Collection
    {
        return $this->combatShiftRepository->all()->map(fn($shift) => CombatShiftDTO::fromModel($shift));
    }

    public function getShiftById(int $id): CombatShiftDTO
    {
        $shift = $this->combatShiftRepository->find($id);

        if (!$shift) {
            throw new ModelNotFoundException("Combat shift with ID {$id} not found");
        }

        return CombatShiftDTO::fromModel($shift);
    }

    public function getActiveShiftByUserId(int $userId): ?CombatShiftDTO
    {
        $shift = $this->combatShiftRepository->findActiveByUserId($userId);

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
        return $this->combatShiftRepository->getActiveShifts()->map(fn($shift) => CombatShiftDTO::fromModel($shift));
    }

    public function createShift(CreateCombatShiftDTO $dto): CombatShiftDTO
    {
        return DB::transaction(function () use ($dto) {
            $shiftModel = $this->combatShiftRepository->create([
                'position_id' => $dto->position_id,
                'status' => $dto->status,
                'started_at' => $dto->started_at,
                'ended_at' => $dto->ended_at,
                'damaged_drones' => $dto->damaged_drones,
                'damaged_coils' => $dto->damaged_coils,
            ]);

            if (!empty($dto->user_ids)) {
                $this->combatShiftRepository->syncUsers($shiftModel, $dto->user_ids);
            } else {
                $this->combatShiftRepository->syncUsers($shiftModel, [Auth::id()]);
            }

            if (!empty($dto->crew)) {
                $this->combatShiftRepository->syncCrew($shiftModel, $dto->crew);
            }

            if (!empty($dto->flights)) {
                $this->combatShiftRepository->syncFlights($shiftModel, $dto->flights);
            }

            $shift = CombatShiftDTO::fromModel($shiftModel->load(['position', 'drones', 'ammunition', 'crew', 'flights']));

            if (!empty($dto->drones)) {
                $this->combatShiftRepository->syncDrones($shiftModel, $this->formatPivotData($shift, $dto->drones, 'drone'));
            }

            if (!empty($dto->ammunition)) {
                $this->combatShiftRepository->syncAmmunition($shiftModel, $this->formatPivotData($shift, $dto->ammunition, 'ammunition'));
            }

            return CombatShiftDTO::fromModel($shiftModel->load(['position', 'drones', 'ammunition', 'crew', 'flights']));
        });
    }

    public function updateShift(int $id, UpdateCombatShiftDTO $dto): CombatShiftDTO
    {
        return DB::transaction(function () use ($id, $dto) {
            $shiftModel = $this->combatShiftRepository->find($id);
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

            $this->combatShiftRepository->update($id, $updateData);

            if (!empty($dto->user_ids)) {
                $this->combatShiftRepository->syncUsers($shiftModel, $dto->user_ids);
            }

            $this->combatShiftRepository->syncCrew($shiftModel, $dto->crew);
            $this->combatShiftRepository->syncFlights($shiftModel, $dto->flights);

            $shift = CombatShiftDTO::fromModel($shiftModel->load(['position', 'drones', 'ammunition', 'crew', 'flights']));

            $this->combatShiftRepository->syncDrones($shiftModel, $this->formatPivotData($shift, $dto->drones, 'drone'));
            $this->combatShiftRepository->syncAmmunition($shiftModel, $this->formatPivotData($shift, $dto->ammunition, 'ammunition'));

            return CombatShiftDTO::fromModel($shiftModel->load(['position', 'drones', 'ammunition', 'crew', 'flights']));
        });
    }

    public function deleteShift(int $id): bool
    {
        return $this->combatShiftRepository->delete($id);
    }

    public function joinShift(int $shiftId, int $userId): void
    {
        $shift = $this->combatShiftRepository->find($shiftId);
        if ($shift && $shift->status === 'opened') {
            $this->combatShiftRepository->attachUser($shift, $userId);
        }
    }

    public function leaveShift(int $shiftId, int $userId): void
    {
        $shift = $this->combatShiftRepository->find($shiftId);
        if ($shift) {
            $this->combatShiftRepository->detachUser($shift, $userId);
        }
    }

    public function finishShift(int $shiftId): void
    {
        $shift = $this->combatShiftRepository->find($shiftId);
        if ($shift && $shift->status === 'opened') {
            $this->combatShiftRepository->update($shiftId, [
                'status' => 'closed',
                'ended_at' => now(),
            ]);
        }
    }

    public function reopenShift(int $shiftId): void
    {
        $shift = $this->combatShiftRepository->find($shiftId);
        if ($shift && $shift->status === 'closed') {
            $this->combatShiftRepository->update($shiftId, [
                'status' => 'opened',
                'ended_at' => null,
            ]);
        }
    }

    public function getDashboardStats(?int $shiftId): array
    {
        if ($shiftId) {
            $flights = $this->flightRepository->getFlightsByShift($shiftId);
        } else {
            $flights = $this->flightRepository->getAllFlights();
        }

        return [
            'total_flights' => $flights->count(),
            'total_combat_flights' => $flights->where('detonation', '!=', 'інше')->count(),
            'total_hits' => $flights->where('result', 'влучання')->count(),
            'total_area_hits' => $flights->where('result', 'удар в районі цілі')->count(),
            'total_misses' => $flights->where('result', 'втрата борту')->count(),
            'total_detonations' => $flights->where('detonation', 'так')->count(),
            'total_non_detonations' => $flights->where('detonation', 'ні')->count(),
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
