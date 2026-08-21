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
        readonly private UgvDroneAdminService           $ugvDroneService,
    )
    {
    }

    /**
     * @param string|null $type
     * @return Collection<CombatShiftDTO>
     */
    public function getAllShifts(?string $type = null): Collection
    {
        return $this->combatShiftRepository->all($type)->map(function($shift) {
            if ($shift->type === PositionTypesEnum::AIR_DEFENCE) {
                $shift->load(['airDefenceDrones', 'airDefenceAmmunition']);
            }
            return CombatShiftDTO::fromModel($shift);
        });
    }

    public function getShiftById(int $id): CombatShiftDTO
    {
        $shift = $this->combatShiftRepository->find($id);

        if (!$shift) {
            throw new ModelNotFoundException("Combat shift with ID {$id} not found");
        }

        if ($shift->type === PositionTypesEnum::AIR_DEFENCE) {
            $shift->load(['airDefenceDrones', 'airDefenceAmmunition']);
        }

        return CombatShiftDTO::fromModel($shift);
    }

    public function getActiveShiftByUserId(int $userId): ?CombatShiftDTO
    {
        $shift = $this->combatShiftRepository->findActiveByUserId($userId);

        if (!$shift) {
            return null;
        }

        if ($shift->type === PositionTypesEnum::AIR_DEFENCE) {
            $shift->load(['airDefenceDrones', 'airDefenceAmmunition']);
        }

        return CombatShiftDTO::fromModel($shift);
    }

    /**
     * @param string|null $type
     * @return Collection<CombatShiftDTO>
     */
    public function getActiveShifts(?string $type = null): Collection
    {
        return $this->combatShiftRepository->getActiveShifts($type)->map(function($shift) {
            if ($shift->type === PositionTypesEnum::AIR_DEFENCE) {
                $shift->load(['airDefenceDrones', 'airDefenceAmmunition']);
            }
            return CombatShiftDTO::fromModel($shift);
        });
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

            if ($shiftModel->type === PositionTypesEnum::AIR_DEFENCE) {
                $this->combatShiftRepository->syncAirDefenceDrones($shiftModel, $this->formatPivotData($dto->drones));
                if (!empty($dto->ammunition)) {
                    $this->combatShiftRepository->syncAirDefenceAmmunition($shiftModel, $this->formatPivotData($dto->ammunition));
                }
            } else {
                $this->combatShiftRepository->syncDrones($shiftModel, $this->formatPivotData($dto->drones));
                if (!empty($dto->ammunition)) {
                    $this->combatShiftRepository->syncAmmunition($shiftModel, $this->formatPivotData($dto->ammunition));
                }
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

            if ($shiftModel->type === PositionTypesEnum::AIR_DEFENCE) {
                return CombatShiftDTO::fromModel($shiftModel->load(['position', 'airDefenceDrones', 'airDefenceAmmunition', 'crew', 'flights']));
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

            if ($shiftModel->type === PositionTypesEnum::AIR_DEFENCE) {
                $this->combatShiftRepository->syncAirDefenceDrones($shiftModel, $this->formatPivotData($dto->drones));
                $this->combatShiftRepository->syncAirDefenceAmmunition($shiftModel, $this->formatPivotData($dto->ammunition));
            } else {
                $this->combatShiftRepository->syncDrones($shiftModel, $this->formatPivotData($dto->drones));
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

            if ($shiftModel->type === PositionTypesEnum::AIR_DEFENCE) {
                return CombatShiftDTO::fromModel($shiftModel->load(['position', 'airDefenceDrones', 'airDefenceAmmunition', 'crew', 'flights']));
            }

            return CombatShiftDTO::fromModel($shiftModel->load(['position', 'drones', 'ammunition', 'crew', 'flights']));
        });
    }

    private function getDroneService(?string $type)
    {
        return match ($type) {
            PositionTypesEnum::RECON->value => $this->reconDroneService,
            PositionTypesEnum::VAMPIRE->value => $this->vampireDroneService,
            PositionTypesEnum::UGV->value => $this->ugvDroneService,
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

    public function resolveDateRange(?string $period = null, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $from = null;
        $to = null;

        if ($dateFrom) {
            try {
                $from = \Carbon\Carbon::parse($dateFrom)->startOfDay();
                $to = $dateTo ? \Carbon\Carbon::parse($dateTo)->endOfDay() : null;
            } catch (\Exception $e) {
                $from = null;
                $to = null;
            }
        } elseif ($period) {
            $periodKey = mb_strtolower(trim($period));
            switch ($periodKey) {
                case 'day':
                case 'today':
                case 'день':
                    $from = \Carbon\Carbon::now()->startOfDay();
                    $to = \Carbon\Carbon::now()->endOfDay();
                    break;
                case 'week':
                case 'тиждень':
                    $from = \Carbon\Carbon::now()->subDays(7)->startOfDay();
                    $to = \Carbon\Carbon::now()->endOfDay();
                    break;
                case 'month':
                case 'місяць':
                    $from = \Carbon\Carbon::now()->subDays(30)->startOfDay();
                    $to = \Carbon\Carbon::now()->endOfDay();
                    break;
                case 'all':
                case 'весь_час':
                case 'за_весь_час':
                    $from = null;
                    $to = null;
                    break;
                default:
                    $from = \Carbon\Carbon::parse('2026-08-20')->startOfDay();
                    $to = null;
                    break;
            }
        } else {
            $from = \Carbon\Carbon::parse('2026-08-20')->startOfDay();
            $to = null;
        }

        return [$from, $to];
    }

    public function getDashboardStats(?string $period = null, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        [$from, $to] = $this->resolveDateRange($period, $dateFrom, $dateTo);
        $activeShiftIds = \App\Models\CombatShift::where('status', 'opened')->pluck('id');

        return [
            'total' => [
                'fpv' => $this->calculateFpvStats($this->getFilteredFpvFlights($from, $to)),
                'recon' => $this->calculateReconStats($this->getFilteredReconFlights($from, $to)),
                'vampire' => $this->calculateVampireStats($this->getFilteredVampireFlights($from, $to)),
                'ugv' => $this->calculateUgvStats($this->getFilteredUgvRaces($from, $to)),
                'air_defence' => $this->calculateAirDefenceStats($this->getFilteredAirDefenceFlights($from, $to)),
            ],
            'active' => [
                'fpv' => $this->calculateFpvStats(\App\Models\CombatShiftFlight::whereIn('combat_shift_id', $activeShiftIds)->get()),
                'recon' => $this->calculateReconStats(\App\Models\ReconFlight::whereIn('combat_shift_id', $activeShiftIds)->get()),
                'vampire' => $this->calculateVampireStats(\App\Models\VampireFlight::whereIn('combat_shift_id', $activeShiftIds)->get()),
                'ugv' => $this->calculateUgvStats(\App\Models\UgvRace::whereIn('combat_shift_id', $activeShiftIds)->get()),
                'air_defence' => $this->calculateAirDefenceStats(\App\Models\AirDefenceFlight::whereIn('position_id', \App\Models\CombatShift::whereIn('id', $activeShiftIds)->pluck('position_id'))->get()), // ППО поки не прив'язано до змін, фільтруємо по позиціях активних змін
            ],
            'positions' => $this->getStatsByPositions($from, $to),
            'active_shifts' => $this->getStatsByActiveShifts(),
            'filters' => [
                'period' => $period,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'from' => $from,
                'to' => $to,
            ],
        ];
    }

    private function applyDateFilter($query, string $field, ?\Carbon\Carbon $from, ?\Carbon\Carbon $to, string $createdAtField = 'created_at'): void
    {
        if ($from && $to) {
            $query->where(function ($q) use ($field, $createdAtField, $from, $to) {
                $q->where(function ($sub) use ($field, $from, $to) {
                    $sub->whereNotNull($field)
                        ->whereBetween($field, [$from, $to]);
                })->orWhere(function ($sub) use ($field, $createdAtField, $from, $to) {
                    $sub->whereNull($field)
                        ->whereBetween($createdAtField, [$from, $to]);
                });
            });
        } elseif ($from) {
            $query->where(function ($q) use ($field, $createdAtField, $from) {
                $q->where(function ($sub) use ($field, $from) {
                    $sub->whereNotNull($field)
                        ->where($field, '>=', $from);
                })->orWhere(function ($sub) use ($field, $createdAtField, $from) {
                    $sub->whereNull($field)
                        ->where($createdAtField, '>=', $from);
                });
            });
        } elseif ($to) {
            $query->where(function ($q) use ($field, $createdAtField, $to) {
                $q->where(function ($sub) use ($field, $to) {
                    $sub->whereNotNull($field)
                        ->where($field, '<=', $to);
                })->orWhere(function ($sub) use ($field, $createdAtField, $to) {
                    $sub->whereNull($field)
                        ->where($createdAtField, '<=', $to);
                });
            });
        }
    }

    private function getFilteredFpvFlights(?\Carbon\Carbon $from, ?\Carbon\Carbon $to): Collection
    {
        $query = \App\Models\CombatShiftFlight::query();
        $this->applyDateFilter($query, 'flight_time', $from, $to, 'created_at');
        return $query->get();
    }

    private function getFilteredReconFlights(?\Carbon\Carbon $from, ?\Carbon\Carbon $to): Collection
    {
        $query = \App\Models\ReconFlight::query();
        $this->applyDateFilter($query, 'flight_time', $from, $to, 'created_at');
        return $query->get();
    }

    private function getFilteredVampireFlights(?\Carbon\Carbon $from, ?\Carbon\Carbon $to): Collection
    {
        $query = \App\Models\VampireFlight::query();
        $this->applyDateFilter($query, 'start_time', $from, $to, 'created_at');
        return $query->get();
    }

    private function getFilteredUgvRaces(?\Carbon\Carbon $from, ?\Carbon\Carbon $to): Collection
    {
        $query = \App\Models\UgvRace::query();
        $this->applyDateFilter($query, 'start_time', $from, $to, 'created_at');
        return $query->get();
    }

    private function getFilteredAirDefenceFlights(?\Carbon\Carbon $from, ?\Carbon\Carbon $to): Collection
    {
        $query = \App\Models\AirDefenceFlight::query();
        $this->applyDateFilter($query, 'start_time', $from, $to, 'created_at');
        return $query->get();
    }

    private function getStatsByActiveShifts(): array
    {
        $activeShifts = \App\Models\CombatShift::where('status', 'opened')
            ->with(['position', 'crew', 'flights', 'reconFlights'])
            ->get();
        $stats = [];

        $vampireFlights = \App\Models\VampireFlight::whereIn('combat_shift_id', $activeShifts->pluck('id'))->get()->groupBy('combat_shift_id');
        $ugvRaces = \App\Models\UgvRace::whereIn('combat_shift_id', $activeShifts->pluck('id'))->get()->groupBy('combat_shift_id');
        $airDefenceFlights = \App\Models\AirDefenceFlight::join('positions', 'air_defence_flights.position_id', '=', 'positions.id')
            ->whereIn('positions.id', $activeShifts->pluck('position_id'))
            ->select('air_defence_flights.*', 'positions.id as position_id')
            ->get();

        foreach ($activeShifts as $shift) {
            $shiftAirDefenceFlights = $airDefenceFlights->filter(function($flight) use ($shift) {
                return $flight->position_id == $shift->position_id &&
                       $flight->created_at >= $shift->started_at &&
                       ($shift->ended_at ? $flight->created_at <= $shift->ended_at : true);
            });

            $stats[] = [
                'id' => $shift->id,
                'position_name' => $shift->position?->name ?? 'Невідома позиція',
                'crew' => $shift->crew->pluck('callsign')->toArray(),
                'type' => $shift->type?->value,
                'fpv' => $this->calculateFpvStats($shift->flights),
                'recon' => $this->calculateReconStats($shift->reconFlights),
                'vampire' => $this->calculateVampireStats($vampireFlights->get($shift->id, collect())),
                'ugv' => $this->calculateUgvStats($ugvRaces->get($shift->id, collect())),
                'air_defence' => $this->calculateAirDefenceStats($shiftAirDefenceFlights),
            ];
        }

        return $stats;
    }

    private function calculateUgvStats(\Illuminate\Support\Collection $flights): array
    {
        $totalFlights = $flights->count();
        $worked = $flights->where('result', 'worked')->count();
        $notWorked = $flights->where('result', 'not_worked')->count();
        $loss = $flights->where('result', 'loss')->count();

        // Ефективність НРК: (Успішні) / (Успішні + Не успішні + Втрати)
        $divisorUgv = $worked + $notWorked + $loss;
        $successRate = $divisorUgv > 0 ? round(($worked / $divisorUgv) * 100, 1) : 0;
        $successRate = min(100, max(0, $successRate));

        return [
            'total_flights' => $totalFlights,
            'worked' => $worked,
            'not_worked' => $notWorked,
            'loss' => $loss,
            'success_rate' => $successRate,
            'combat_flights_for_success' => $divisorUgv,
        ];
    }

    private function calculateAirDefenceStats(\Illuminate\Support\Collection $flights): array
    {
        $totalFlights = $flights->count();
        $hits = $flights->where('result', 'влучання')->count();
        $misses = $flights->where('result', 'промах')->count();
        $loosed = $flights->where('result', 'втрата борта')->count();

        // Ефективність влучань: (Влучання) / (Влучання + Промахи + Втрати борта)
        $divisorHit = $hits + $misses + $loosed;
        $hitRate = $divisorHit > 0 ? round(($hits / $divisorHit) * 100, 1) : 0;
        $hitRate = min(100, max(0, $hitRate));

        return [
            'total_flights' => $totalFlights,
            'total_hits' => $hits,
            'total_misses' => $misses,
            'total_loosed' => $loosed,
            'hit_rate' => $hitRate,
            'combat_flights_for_hit' => $divisorHit,
        ];
    }

    private function calculateFpvStats(\Illuminate\Support\Collection $flights): array
    {
        $totalFlights = $flights->count();

        // По місіях
        $strikeFlights = $flights->where('mission', 'strike');
        $patrolFlights = $flights->where('mission', 'patrol');
        $logisticsFlights = $flights->where('mission', 'logistics');

        // Основні результати (Strike)
        $hits = $flights->where('result', 'влучання')->count();
        $areaHits = $flights->where('result', 'удар в районі цілі')->count();

        // Нові результати (Patrol / Logistics)
        $worked = $flights->where('result', 'відпрацювали')->count();
        $logisticsSpent = $flights->where('result', 'відпрацювали (витрата борту)')->count();
        $logisticsReturned = $flights->where('result', 'відпрацювали (повернули борт)')->count();

        $misses = $flights->where('result', 'втрата борту')->count();

        $detonations = $flights->where('detonation', 'так')->count();
        // При підрахунку тих, що не розірвалися, не враховуємо ті, де була втрата борту або логістика (де детонація не обов'язкова)
        $nonDetonations = $flights->where('detonation', 'ні')
            ->where('result', '!=', 'втрата борту')
            ->where('mission', '!=', 'logistics')
            ->count();

        // Ефективність влучань: (Влучання + 0.5 * В районі цілі + Відпрацювали + Логістика) / (Влучання + В районі цілі + Відпрацювали + Логістика + Втрати)
        // Втрати бортів негативно впливають, оскільки вони в знаменнику.
        $successActions = $hits + $worked + $logisticsSpent + $logisticsReturned;
        $divisorHit = $successActions + $areaHits + $misses;
        $positivePoints = $successActions + ($areaHits * 0.5);
        $hitRate = $divisorHit > 0 ? round(($positivePoints / $divisorHit) * 100, 1) : 0;
        $hitRate = min(100, max(0, $hitRate));

        // Надійність БК: (Детонації) / (Детонації + Не розірвався)
        // Враховуємо тільки ті вильоти, де точно відомо (так/ні), ігноруючи 'не відомо'
        $divisorDetonation = $detonations + $nonDetonations;
        $detonationRate = $divisorDetonation > 0 ? round(($detonations / $divisorDetonation) * 100, 1) : 0;
        $detonationRate = min(100, max(0, $detonationRate));

        return [
            'total_flights' => $totalFlights,
            'total_hits' => $hits,
            'total_area_hits' => $areaHits,
            'total_misses' => $misses,
            'total_worked' => $worked,
            'total_logistics_spent' => $logisticsSpent,
            'total_logistics_returned' => $logisticsReturned,
            'total_detonations' => $detonations,
            'total_non_detonations' => $nonDetonations,
            'hit_rate' => $hitRate,
            'detonation_rate' => $detonationRate,
            'combat_flights_for_hit' => $divisorHit,
            'combat_flights_for_detonation' => $divisorDetonation,

            // Статистика по місіях для деталізації
            'missions' => [
                'strike' => [
                    'total' => $strikeFlights->count(),
                    'hits' => $strikeFlights->where('result', 'влучання')->count(),
                    'area_hits' => $strikeFlights->where('result', 'удар в районі цілі')->count(),
                    'misses' => $strikeFlights->where('result', 'втрата борту')->count(),
                ],
                'patrol' => [
                    'total' => $patrolFlights->count(),
                    'worked' => $patrolFlights->where('result', 'відпрацювали')->count(),
                    'hits' => $patrolFlights->where('result', 'влучання')->count(),
                    'area_hits' => $patrolFlights->where('result', 'удар в районі цілі')->count(),
                    'misses' => $patrolFlights->where('result', 'втрата борту')->count(),
                ],
                'logistics' => [
                    'total' => $logisticsFlights->count(),
                    'spent' => $logisticsFlights->where('result', 'відпрацювали (витрата борту)')->count(),
                    'returned' => $logisticsFlights->where('result', 'відпрацювали (повернули борт)')->count(),
                    'misses' => $logisticsFlights->where('result', 'втрата борту')->count(),
                ]
            ]
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
        $success = $flights->where('result', 'worked')->count();
        $failed = $flights->where('result', 'not_worked')->count();
        $loosed = $flights->where('result', 'loss')->count();

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

    private function getStatsByPositions(?\Carbon\Carbon $from = null, ?\Carbon\Carbon $to = null): array
    {
        $positions = \App\Models\Position::all();
        $stats = [];

        $fpvQuery = \App\Models\CombatShiftFlight::join('combat_shifts', 'combat_shift_flights.combat_shift_id', '=', 'combat_shifts.id')
            ->select('combat_shift_flights.*', 'combat_shifts.position_id');
        $this->applyDateFilter($fpvQuery, 'combat_shift_flights.flight_time', $from, $to, 'combat_shift_flights.created_at');
        $fpvFlights = $fpvQuery->get()->groupBy('position_id');

        $reconQuery = \App\Models\ReconFlight::join('combat_shifts', 'recon_flights.combat_shift_id', '=', 'combat_shifts.id')
            ->select('recon_flights.*', 'combat_shifts.position_id');
        $this->applyDateFilter($reconQuery, 'recon_flights.flight_time', $from, $to, 'recon_flights.created_at');
        $reconFlights = $reconQuery->get()->groupBy('position_id');

        $vampireQuery = \App\Models\VampireFlight::join('combat_shifts', 'vampire_flights.combat_shift_id', '=', 'combat_shifts.id')
            ->select('vampire_flights.*', 'combat_shifts.position_id');
        $this->applyDateFilter($vampireQuery, 'vampire_flights.start_time', $from, $to, 'vampire_flights.created_at');
        $vampireFlights = $vampireQuery->get()->groupBy('position_id');

        $ugvQuery = \App\Models\UgvRace::join('combat_shifts', 'ugv_races.combat_shift_id', '=', 'combat_shifts.id')
            ->select('ugv_races.*', 'combat_shifts.position_id');
        $this->applyDateFilter($ugvQuery, 'ugv_races.start_time', $from, $to, 'ugv_races.created_at');
        $ugvFlights = $ugvQuery->get()->groupBy('position_id');

        foreach ($positions as $position) {
            $stats[$position->id] = [
                'name' => $position->name,
                'type' => $position->type,
                'fpv' => $this->calculateFpvStats($fpvFlights->get($position->id, collect())),
                'recon' => $this->calculateReconStats($reconFlights->get($position->id, collect())),
                'vampire' => $this->calculateVampireStats($vampireFlights->get($position->id, collect())),
                'ugv' => $this->calculateUgvStats($ugvFlights->get($position->id, collect())),
            ];
        }

        return $stats;
    }

    public function updateAmmunitionQuantity(int $shiftId, int $ammunitionId, int $change): void
    {
        $shift = $this->combatShiftRepository->find($shiftId);
        if (!$shift) return;

        if ($shift->type === PositionTypesEnum::AIR_DEFENCE) {
            $currentQuantity = $shift->airDefenceAmmunition()->where('air_defence_ammunition_id', $ammunitionId)->first()?->pivot->quantity ?? 0;
            $newQuantity = max(0, $currentQuantity + $change);
            $shift->airDefenceAmmunition()->updateExistingPivot($ammunitionId, ['quantity' => $newQuantity]);
        } else {
            $currentQuantity = $shift->ammunition()->where('ammunition_id', $ammunitionId)->first()?->pivot->quantity ?? 0;
            $newQuantity = max(0, $currentQuantity + $change);
            $shift->ammunition()->updateExistingPivot($ammunitionId, ['quantity' => $newQuantity]);
        }
    }

    public function updateDroneQuantity(int $shiftId, int $droneId, int $change): void
    {
        $shift = $this->combatShiftRepository->find($shiftId);
        if (!$shift) return;

        if ($shift->type === PositionTypesEnum::AIR_DEFENCE) {
            $currentQuantity = $shift->airDefenceDrones()->where('air_defence_drone_id', $droneId)->first()?->pivot->quantity ?? 0;
            $newQuantity = max(0, $currentQuantity + $change);
            $shift->airDefenceDrones()->updateExistingPivot($droneId, ['quantity' => $newQuantity]);
        } else {
            $currentQuantity = $shift->drones()->where('drone_id', $droneId)->first()?->pivot->quantity ?? 0;
            $newQuantity = max(0, $currentQuantity + $change);
            $shift->drones()->updateExistingPivot($droneId, ['quantity' => $newQuantity]);
        }
    }

    public function getDefaultReportRange(): array
    {
        $now = now();
        if ($now->hour < 14) {
            $from = $now->copy()->subDay()->setTime(14, 0, 0)->format('Y-m-d\TH:i');
            $to = $now->copy()->setTime(14, 0, 0)->format('Y-m-d\TH:i');
        } else {
            $from = $now->copy()->setTime(14, 0, 0)->format('Y-m-d\TH:i');
            $to = $now->copy()->addDay()->setTime(14, 0, 0)->format('Y-m-d\TH:i');
        }

        return [$from, $to];
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
