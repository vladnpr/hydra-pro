<?php

namespace App\Services;

use App\DTOs\CombatShiftDTO;
use App\DTOs\CreateCombatShiftDTO;
use App\DTOs\UpdateCombatShiftDTO;
use App\Enums\PositionTypesEnum;
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
        readonly private VampireDroneAdminService       $vampireDroneService,
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

            if (!empty($dto->new_drones)) {
                $droneService = $this->getDroneService($shiftModel->type?->value);
                if ($droneService) {
                    foreach ($dto->new_drones as $droneData) {
                        $droneData['position_id'] = $dto->position_id;
                        $droneService->createDrone($droneData);
                    }
                }
            }

            if (!empty($dto->existing_drones)) {
                $droneService = $this->getDroneService($shiftModel->type?->value);
                if ($droneService) {
                    foreach ($dto->existing_drones as $droneData) {
                        $droneService->updateDrone((int)$droneData['id'], [
                            'status' => $droneData['status'],
                            'lost_at' => $droneData['lost_at'] ?? null,
                            'shift_type' => $droneData['shift_type']
                        ]);
                    }
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

            if ($dto->request_source !== 'admin_edit' || !empty($dto->flights)) {
                $this->combatShiftRepository->syncFlights($shiftModel, $dto->flights);
            }

            $this->combatShiftRepository->syncDrones($shiftModel, $this->formatPivotData($dto->drones));

            $this->combatShiftRepository->syncAmmunition($shiftModel, $this->formatPivotData($dto->ammunition));

            if (!empty($dto->new_drones)) {
                $droneService = $this->getDroneService($shiftModel->type?->value);
                if ($droneService) {
                    foreach ($dto->new_drones as $droneData) {
                        $droneData['position_id'] = $dto->position_id;
                        $droneService->createDrone($droneData);
                    }
                }
            }

            if (!empty($dto->existing_drones)) {
                $droneService = $this->getDroneService($shiftModel->type?->value);
                if ($droneService) {
                    foreach ($dto->existing_drones as $droneData) {
                        $droneService->updateDrone((int)$droneData['id'], [
                            'status' => $droneData['status'],
                            'lost_at' => $droneData['lost_at'] ?? null,
                            'shift_type' => $droneData['shift_type']
                        ]);
                    }
                }
            }

            return CombatShiftDTO::fromModel($shiftModel->load(['position', 'drones', 'ammunition', 'crew', 'flights']));
        });
    }

    private function getDroneService(?string $type)
    {
        return match ($type) {
            PositionTypesEnum::RECON->value => $this->reconDroneService,
            PositionTypesEnum::VAMPIRE->value => $this->vampireDroneService,
            default => null,
        };
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

    public function getDashboardStats(): array
    {
        $fpvAllFlights = \App\Models\CombatShiftFlight::all();
        $reconAllFlights = \App\Models\ReconFlight::all();
        $vampireAllFlights = \App\Models\VampireFlight::all();

        $activeShiftIds = \App\Models\CombatShift::where('status', 'opened')->pluck('id');
        $fpvActiveFlights = \App\Models\CombatShiftFlight::whereIn('combat_shift_id', $activeShiftIds)->get();
        $reconActiveFlights = \App\Models\ReconFlight::whereIn('combat_shift_id', $activeShiftIds)->get();
        $vampireActiveFlights = \App\Models\VampireFlight::whereIn('combat_shift_id', $activeShiftIds)->get();

        return [
            'total' => [
                'fpv' => $this->calculateFpvStats($fpvAllFlights),
                'recon' => $this->calculateReconStats($reconAllFlights),
                'vampire' => $this->calculateVampireStats($vampireAllFlights),
            ],
            'active' => [
                'fpv' => $this->calculateFpvStats($fpvActiveFlights),
                'recon' => $this->calculateReconStats($reconActiveFlights),
                'vampire' => $this->calculateVampireStats($vampireActiveFlights),
            ],
            'positions' => $this->getStatsByPositions(),
            'active_shifts' => $this->getStatsByActiveShifts(),
        ];
    }

    private function getStatsByActiveShifts(): array
    {
        $activeShifts = \App\Models\CombatShift::where('status', 'opened')->with(['position', 'crew'])->get();
        $stats = [];

        foreach ($activeShifts as $shift) {
            $fpvFlights = $shift->flights;
            $reconFlights = $shift->reconFlights;
            $vampireFlights = \App\Models\VampireFlight::where('combat_shift_id', $shift->id)->get();

            $stats[] = [
                'id' => $shift->id,
                'position_name' => $shift->position?->name ?? 'Невідома позиція',
                'crew' => $shift->crew->pluck('callsign')->toArray(),
                'type' => $shift->type?->value,
                'fpv' => $this->calculateFpvStats($fpvFlights),
                'recon' => $this->calculateReconStats($reconFlights),
                'vampire' => $this->calculateVampireStats($vampireFlights),
            ];
        }

        return $stats;
    }

    private function calculateFpvStats(\Illuminate\Support\Collection $flights): array
    {
        $totalFlights = $flights->count();
        $hits = $flights->where('result', 'влучання')->count();
        $areaHits = $flights->where('result', 'удар в районі цілі')->count();
        $misses = $flights->where('result', 'втрата борту')->count();

        $detonations = $flights->where('detonation', 'так')->count();
        // При підрахунку тих, що не розірвалися, не враховуємо ті, де була втрата борту
        $nonDetonations = $flights->where('detonation', 'ні')->where('result', '!=', 'втрата борту')->count();

        // Ефективність влучань: (Влучання + 0.5 * В районі цілі) / (Влучання + В районі цілі + Втрати)
        // Втрати бортів негативно впливають, оскільки вони в знаменнику.
        $divisorHit = $hits + $areaHits + $misses;
        $positivePoints = $hits + ($areaHits * 0.5);
        $hitRate = $divisorHit > 0 ? round(($positivePoints / $divisorHit) * 100, 1) : 0;
        $hitRate = min(100, max(0, $hitRate));

        // Надійність БК: (Детонації) / (Детонації + Не розірвався)
        // Враховуємо тільки ті вильоти, де точно відомо (так/ні), ігноруючи 'інше'
        $divisorDetonation = $detonations + $nonDetonations;
        $detonationRate = $divisorDetonation > 0 ? round(($detonations / $divisorDetonation) * 100, 1) : 0;
        $detonationRate = min(100, max(0, $detonationRate));

        return [
            'total_flights' => $totalFlights,
            'total_hits' => $hits,
            'total_area_hits' => $areaHits,
            'total_misses' => $misses,
            'total_detonations' => $detonations,
            'total_non_detonations' => $nonDetonations,
            'hit_rate' => $hitRate,
            'detonation_rate' => $detonationRate,
            'combat_flights_for_hit' => $divisorHit,
            'combat_flights_for_detonation' => $divisorDetonation,
        ];
    }

    private function calculateReconStats(\Illuminate\Support\Collection $flights): array
    {
        $totalFlights = $flights->count();
        $success = $flights->where('result', \App\Enums\ReconMissionResultsEnum::SUCCESS)->count();
        $loosed = $flights->where('result', \App\Enums\ReconMissionResultsEnum::BOARD_LOOSED)->count();
        $other = $flights->where('result', \App\Enums\ReconMissionResultsEnum::OTHER)->count();

        // Ефективність розвідки: (Успішні) / (Успішні + Втрати)
        // Втрати бортів негативно впливають, оскільки вони в знаменнику.
        // Ми можемо також включити 'інше' в знаменник, якщо це вважається "не успіхом"
        $divisorRecon = $success + $loosed;
        $successRate = $divisorRecon > 0 ? round(($success / $divisorRecon) * 100, 1) : 0;
        $successRate = min(100, max(0, $successRate));

        return [
            'total_flights' => $totalFlights,
            'total_success' => $success,
            'total_loosed' => $loosed,
            'total_other' => $other,
            'success_rate' => $successRate,
            'combat_flights_for_success' => $divisorRecon,
        ];
    }

    private function calculateVampireStats(\Illuminate\Support\Collection $flights): array
    {
        $totalFlights = $flights->count();
        $success = $flights->where('result', 'успішно')->count();
        $failed = $flights->where('result', 'не успішно')->count();
        $loosed = $flights->where('result', 'втрата борту')->count();

        // Ефективність Вампіра: (Успішні) / (Успішні + Не успішні + Втрати)
        $divisorVampire = $success + $failed + $loosed;
        $successRate = $divisorVampire > 0 ? round(($success / $divisorVampire) * 100, 1) : 0;
        $successRate = min(100, max(0, $successRate));

        return [
            'total_flights' => $totalFlights,
            'total_success' => $success,
            'total_failed' => $failed,
            'total_loosed' => $loosed,
            'success_rate' => $successRate,
            'combat_flights_for_success' => $divisorVampire,
        ];
    }

    private function getStatsByPositions(): array
    {
        $positions = \App\Models\Position::all();
        $stats = [];

        foreach ($positions as $position) {
            $shiftIds = \App\Models\CombatShift::where('position_id', $position->id)->pluck('id');

            $fpvFlights = \App\Models\CombatShiftFlight::whereIn('combat_shift_id', $shiftIds)->get();
            $reconFlights = \App\Models\ReconFlight::whereIn('combat_shift_id', $shiftIds)->get();

            $stats[$position->id] = [
                'name' => $position->name,
                'type' => $position->type,
                'fpv' => $this->calculateFpvStats($fpvFlights),
                'recon' => $this->calculateReconStats($reconFlights),
            ];
        }

        return $stats;
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
