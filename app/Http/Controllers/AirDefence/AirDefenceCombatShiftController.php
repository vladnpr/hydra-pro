<?php

namespace App\Http\Controllers\AirDefence;

use App\DTOs\CreateCombatShiftDTO;
use App\DTOs\UpdateCombatShiftDTO;
use App\Enums\PositionTypesEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\AirDefenceCombatShiftStoreRequest;
use App\Http\Requests\AirDefenceCombatShiftUpdateRequest;
use App\Repositories\Contracts\PositionRepositoryInterface;
use App\Services\CombatShiftsAdminService;
use App\Models\AirDefenceDrone;
use App\Models\AirDefenceAmmunition;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AirDefenceCombatShiftController extends Controller
{
    public function __construct(
        private readonly CombatShiftsAdminService      $combatShiftsAdminService,
        private readonly PositionRepositoryInterface   $positionRepository
    ) {
        $this->middleware(function ($request, $next) {
            if (Gate::denies('manage-air-defence')) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function index()
    {
        $shifts = $this->combatShiftsAdminService->getAllShifts(PositionTypesEnum::AIR_DEFENCE->value);
        $userActiveShift = $this->combatShiftsAdminService->getActiveShiftByUserId(Auth::id());

        if ($userActiveShift && $userActiveShift->type !== PositionTypesEnum::AIR_DEFENCE->value) {
            $userActiveShift = null;
        }

        return view('admin.air_defence.combat_shifts.index', compact('shifts', 'userActiveShift'));
    }

    public function create()
    {
        if ($activeShift = $this->combatShiftsAdminService->getActiveShiftByUserId(Auth::id())) {
             return redirect()->route('air-defence.combat_shifts.index')
                ->with('error', 'У вас вже є відкрите чергування (тип: ' . $activeShift->type . '). Спочатку завершіть його.');
        }

        $users = \App\Models\User::all();
        $positions = $this->positionRepository->getActive(PositionTypesEnum::AIR_DEFENCE->value);
        $drones = AirDefenceDrone::where('status', 'active')->get();
        $ammunition = AirDefenceAmmunition::where('status', 'active')->get();

        return view('admin.air_defence.combat_shifts.create', compact('positions', 'users', 'drones', 'ammunition'));
    }

    public function store(AirDefenceCombatShiftStoreRequest $request)
    {
        if ($this->combatShiftsAdminService->getActiveShiftByUserId(Auth::id())) {
            return redirect()->route('air-defence.combat_shifts.index')
                ->with('error', 'У вас вже є відкрите чергування.');
        }

        $dto = CreateCombatShiftDTO::fromRequest($request);
        $this->combatShiftsAdminService->createShift($dto);

        return redirect()->route('air-defence.combat_shifts.index')
            ->with('success', 'Чергування успішно розпочато');
    }

    public function show(int $id)
    {
        $shift = $this->combatShiftsAdminService->getShiftById($id);
        if ($shift->type !== PositionTypesEnum::AIR_DEFENCE->value) {
            abort(404);
        }
        $userActiveShift = $this->combatShiftsAdminService->getActiveShiftByUserId(Auth::id());
        return view('admin.air_defence.combat_shifts.show', compact('shift', 'userActiveShift'));
    }

    public function edit(int $id)
    {
        $shift = $this->combatShiftsAdminService->getShiftById($id);
        if ($shift->type !== PositionTypesEnum::AIR_DEFENCE->value) {
            abort(404);
        }

        $users = \App\Models\User::all();
        $positions = $this->positionRepository->getActive(PositionTypesEnum::AIR_DEFENCE->value);
        $drones = AirDefenceDrone::where('status', 'active')->get();
        $ammunition = AirDefenceAmmunition::where('status', 'active')->get();

        $currentDrones = [];
        foreach ($shift->drones as $d) {
            $currentDrones[$d['id']] = $d['quantity'];
        }

        $currentAmmunition = [];
        foreach ($shift->ammunition as $a) {
            $currentAmmunition[$a['id']] = $a['quantity'];
        }

        return view('admin.air_defence.combat_shifts.edit', compact('shift', 'positions', 'users', 'drones', 'ammunition', 'currentDrones', 'currentAmmunition'));
    }

    public function update(AirDefenceCombatShiftUpdateRequest $request, int $id)
    {
        $dto = UpdateCombatShiftDTO::fromRequest($request);
        $this->combatShiftsAdminService->updateShift($id, $dto);

        return redirect()->route('air-defence.combat_shifts.index')
            ->with('success', 'Чергування успішно оновлено');
    }

    public function destroy(int $id)
    {
        $this->combatShiftsAdminService->deleteShift($id);
        return redirect()->route('air-defence.combat_shifts.index')
            ->with('success', 'Чергування видалено');
    }

    public function join(int $id)
    {
        $this->combatShiftsAdminService->joinShift($id, Auth::id());
        return redirect()->back()->with('success', 'Ви приєдналися до чергування');
    }

    public function leave(int $id)
    {
        $this->combatShiftsAdminService->leaveShift($id, Auth::id());
        return redirect()->back()->with('success', 'Ви залишили чергування');
    }

    public function finish(int $id)
    {
        $this->combatShiftsAdminService->finishShift($id);
        return redirect()->route('air-defence.combat_shifts.index')->with('success', 'Чергування завершено');
    }

    public function report(int $id)
    {
        $shift = $this->combatShiftsAdminService->getShiftById($id);
        if ($shift->type !== PositionTypesEnum::AIR_DEFENCE->value) {
            abort(404);
        }
        $shiftDate = \Carbon\Carbon::parse($shift->started_at);
        $now = \Carbon\Carbon::now();
        $dayNumber = (int) $shiftDate->diffInDays($now) + 1;
        return view('admin.air_defence.combat_shifts.report', compact('shift', 'dayNumber'));
    }

    public function flightsReport(int $id)
    {
        $shift = $this->combatShiftsAdminService->getShiftById($id);
        if ($shift->type !== PositionTypesEnum::AIR_DEFENCE->value) {
            abort(404);
        }
        return view('admin.air_defence.combat_shifts.flights_report', compact('shift'));
    }
}
