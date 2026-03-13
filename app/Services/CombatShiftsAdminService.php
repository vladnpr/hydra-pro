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
        readonly private ReconDroneAdminService         $reconDroneService,
    )
    {
    }

    /**
     * @param string|null $type
     * @return Collection<CombatShiftDTO>
     */
    public function getAllShifts(?string $type = null): Collection
    {
        return $this->combatShiftRepository->all($type)->map(fn($shift) => CombatShiftDTO::fromModel($shift));
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
     * @param string|null $type
     * @return Collection<CombatShiftDTO>
     */
    public function getActiveShifts(?string $type = null): Collection
    {
        return $this->combatShiftRepository->getActiveShifts($type)->map(fn($shift) => CombatShiftDTO::fromModel($shift));
    }

    public function createShift(CreateCombatShiftDTO $dto): CombatShiftDTO
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($dto) {
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
                $this->combatShiftRepository->syncUsers($shiftModel, [\Illuminate\Support\Facades\Auth::id()]);
            }

            if (!empty($dto->crew)) {
                $this->combatShiftRepository->syncCrew($shiftModel, $dto->crew);
            }

            if (!empty($dto->flights)) {
                $this->combatShiftRepository->syncFlights($shiftModel, $dto->flights);
            }

            $this->combatShiftRepository->syncDrones($shiftModel, $this->formatPivotData($dto->drones));

            if (!empty($dto->ammunition)) {
                $this->combatShiftRepository->syncAmmunition($shiftModel, $this->formatPivotData($dto->ammunition));
            }

            if (!empty($dto->new_recon_drones)) {
                foreach ($dto->new_recon_drones as $droneData) {
                    $droneData['position_id'] = $dto->position_id;
                    $this->reconDroneService->createDrone($droneData);
                }
            }

            if (!empty($dto->existing_recon_drones)) {
                foreach ($dto->existing_recon_drones as $droneData) {
                    $this->reconDroneService->updateDrone((int)$droneData['id'], [
                        'status' => $droneData['status'],
                        'shift_type' => $droneData['shift_type']
                    ]);
                }
            }

            return CombatShiftDTO::fromModel($shiftModel->load(['position', 'drones', 'ammunition', 'crew', 'flights']));
        });
    }

    public function updateShift(int $id, UpdateCombatShiftDTO $dto): CombatShiftDTO
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($id, $dto) {
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

            $this->combatShiftRepository->syncDrones($shiftModel, $this->formatPivotData($dto->drones));

            $this->combatShiftRepository->syncAmmunition($shiftModel, $this->formatPivotData($dto->ammunition));

            if (!empty($dto->new_recon_drones)) {
                foreach ($dto->new_recon_drones as $droneData) {
                    $droneData['position_id'] = $dto->position_id;
                    $this->reconDroneService->createDrone($droneData);
                }
            }

            if (!empty($dto->existing_recon_drones)) {
                foreach ($dto->existing_recon_drones as $droneData) {
                    $this->reconDroneService->updateDrone((int)$droneData['id'], [
                        'status' => $droneData['status'],
                        'shift_type' => $droneData['shift_type']
                    ]);
                }
            }

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

    public function updateAmmunitionQuantity(int $shiftId, int $ammunitionId, int $change): void
    {
        $shift = $this->combatShiftRepository->find($shiftId);
        if (!$shift) return;

        $currentQuantity = $shift->ammunition()->where('ammunition_id', $ammunitionId)->first()?->pivot->quantity ?? 0;
        $newQuantity = max(0, $currentQuantity + $change);

        $shift->ammunition()->updateExistingPivot($ammunitionId, ['quantity' => $newQuantity]);
    }

    public function updateDroneQuantity(int $shiftId, int $droneId, int $change): void
    {
        $shift = $this->combatShiftRepository->find($shiftId);
        if (!$shift) return;

        $currentQuantity = $shift->drones()->where('drone_id', $droneId)->first()?->pivot->quantity ?? 0;
        $newQuantity = max(0, $currentQuantity + $change);

        $shift->drones()->updateExistingPivot($droneId, ['quantity' => $newQuantity]);
    }

    private function formatPivotData(array $items): array
    {
        $formatted = [];
        foreach ($items as $key => $value) {
            if (is_array($value)) {
                $formatted[$key] = $value;
            } elseif (is_numeric($key) && is_numeric($value)) {
                // This is id => quantity (Ammunition or FPV drones)
                // Note: is_numeric($key) will be true for string numeric keys like "123"
                if ($value > 0) {
                    $formatted[$key] = ['quantity' => (int) $value];
                }
            }
        }
        return $formatted;
    }
}
