<?php

namespace App\Http\Controllers\FPV;

use App\Http\Controllers\Controller;

use App\DTOs\CreateCombatShiftDTO;
use App\DTOs\UpdateCombatShiftDTO;
use App\Http\Requests\CombatShiftStoreRequest;
use App\Http\Requests\CombatShiftUpdateRequest;
use App\Services\CombatShiftsAdminService;
use App\Repositories\Contracts\PositionRepositoryInterface;
use App\Repositories\Contracts\DroneRepositoryInterface;
use App\Repositories\Contracts\AmmunitionRepositoryInterface;
use App\Enums\PositionTypesEnum;

class FpvCombatShiftController extends Controller
{
    public function __construct(
        private readonly CombatShiftsAdminService      $combatShiftsAdminService,
        private readonly PositionRepositoryInterface   $positionRepository,
        private readonly DroneRepositoryInterface      $droneRepository,
        private readonly AmmunitionRepositoryInterface $ammunitionRepository
    ) {
        $this->middleware(function ($request, $next) {
            $reportMethods = ['activeShiftsReports', 'report', 'flightsReport', 'activeFlightsReport', 'activeRemainsReport', 'show'];
            $currentMethod = $request->route()->getActionMethod();

            if (in_array($currentMethod, $reportMethods)) {
                if (\Illuminate\Support\Facades\Gate::denies('view-reports') && \Illuminate\Support\Facades\Gate::denies('manage-combat')) {
                    abort(403);
                }
            } else {
                if (\Illuminate\Support\Facades\Gate::denies('manage-combat')) {
                    abort(403);
                }
            }

            return $next($request);
        });
    }

    public function index()
    {
        $shifts = $this->combatShiftsAdminService->getAllShifts(PositionTypesEnum::FPV->value);
        $userActiveShift = $this->combatShiftsAdminService->getActiveShiftByUserId(\Illuminate\Support\Facades\Auth::id());

        if ($userActiveShift && $userActiveShift->type !== PositionTypesEnum::FPV->value) {
            $userActiveShift = null;
        }

        return view('admin.combat_shifts.index', compact('shifts', 'userActiveShift'));
    }

    public function activeShiftsReports()
    {
        $activeShifts = $this->combatShiftsAdminService->getActiveShifts();
        return view('admin.combat_shifts.active_reports', compact('activeShifts'));
    }

    public function create()
    {
        if ($this->combatShiftsAdminService->getActiveShiftByUserId(\Illuminate\Support\Facades\Auth::id())) {
            return redirect()->route('combat_shifts.index')
                ->with('error', 'У вас вже є відкрите чергування. Спочатку завершіть його.');
        }

        $users = \App\Models\User::all();
        $positions = $this->positionRepository->getActive(PositionTypesEnum::FPV->value);
        $drones = $this->droneRepository->getActive();
        $ammunition = $this->ammunitionRepository->getActive(PositionTypesEnum::FPV->value);
        return view('admin.combat_shifts.create', compact('positions', 'drones', 'ammunition', 'users'));
    }

    public function store(CombatShiftStoreRequest $request)
    {
        if ($this->combatShiftsAdminService->getActiveShiftByUserId(\Illuminate\Support\Facades\Auth::id())) {
            return redirect()->route('combat_shifts.index')
                ->with('error', 'У вас вже є відкрите чергування.');
        }

        $dto = CreateCombatShiftDTO::fromRequest($request);
        $this->combatShiftsAdminService->createShift($dto);

        return redirect()->route('combat_shifts.index')
            ->with('success', 'Чергування успішно розпочато');
    }

    public function show(int $id)
    {
        $shift = $this->combatShiftsAdminService->getShiftById($id);
        $userActiveShift = $this->combatShiftsAdminService->getActiveShiftByUserId(\Illuminate\Support\Facades\Auth::id());
        return view('admin.combat_shifts.show', compact('shift', 'userActiveShift'));
    }

    public function report(int $id)
    {
        $shift = $this->combatShiftsAdminService->getShiftById($id);

        // Номер дня вираховується від дня старту зміни
        // Перший день зміни - це День 1
        $shiftDate = \Carbon\Carbon::parse($shift->started_at);
        $now = \Carbon\Carbon::now();
        $dayNumber = (int) $shiftDate->diffInDays($now) + 1;

        return view('admin.combat_shifts.report', compact('shift', 'dayNumber'));
    }

    public function flightsReport(int $id, \Illuminate\Http\Request $request)
    {
        $shift = $this->combatShiftsAdminService->getShiftById($id);
        $date = $request->query('date', now()->format('Y-m-d'));

        // Отримуємо польоти за обрану дату
        $flights = $shift->flights[$date] ?? [];

        return view('admin.combat_shifts.flights_report', compact('shift', 'date', 'flights'));
    }

    public function activeFlightsReport(\Illuminate\Http\Request $request)
    {
        $activeShift = $this->combatShiftsAdminService->getActiveShiftByUserId(\Illuminate\Support\Facades\Auth::id());

        if (!$activeShift) {
            return redirect()->route('flight_operations.index')
                ->with('error', 'У вас немає активної зміни.');
        }

        if ($activeShift->type === PositionTypesEnum::RECON->value) {
            return redirect()->route('recon.combat_shifts.active_flights_report', $request->all());
        }

        return $this->flightsReport($activeShift->id, $request);
    }

    public function activeRemainsReport()
    {
        $activeShift = $this->combatShiftsAdminService->getActiveShiftByUserId(\Illuminate\Support\Facades\Auth::id());

        if (!$activeShift) {
            return redirect()->route('flight_operations.index')
                ->with('error', 'У вас немає активної зміни.');
        }

        if ($activeShift->type === PositionTypesEnum::RECON->value) {
            return redirect()->route('recon.combat_shifts.report', $activeShift->id);
        }

        return $this->report($activeShift->id);
    }

    public function edit(int $id)
    {
        $shift = $this->combatShiftsAdminService->getShiftById($id);
        if ($shift->type !== PositionTypesEnum::FPV->value) {
            abort(404);
        }
        $users = \App\Models\User::all();
        $positions = $this->positionRepository->getActive(PositionTypesEnum::FPV->value);
        $drones = $this->droneRepository->getActive();
        $ammunition = $this->ammunitionRepository->getActive(PositionTypesEnum::FPV->value);

        // Prepare quantities for the form
        $currentDrones = [];
        foreach ($shift->drones as $d) {
            $currentDrones[$d['id']] = $d['quantity'];
        }

        $currentAmmunition = [];
        foreach ($shift->ammunition as $a) {
            $currentAmmunition[$a['id']] = $a['quantity'];
        }

        return view('admin.combat_shifts.edit', compact('shift', 'positions', 'drones', 'ammunition', 'currentDrones', 'currentAmmunition', 'users'));
    }

    public function update(CombatShiftUpdateRequest $request, int $id)
    {
        $dto = UpdateCombatShiftDTO::fromRequest($request);
        $this->combatShiftsAdminService->updateShift($id, $dto);

        return redirect()->route('combat_shifts.index')
            ->with('success', 'Чергування успішно оновлено');
    }

    public function destroy(int $id)
    {
        $this->combatShiftsAdminService->deleteShift($id);

        return redirect()->route('combat_shifts.index')
            ->with('success', 'Чергування успішно видалено');
    }

    public function join(int $id)
    {
        $userId = \Illuminate\Support\Facades\Auth::id();

        if ($this->combatShiftsAdminService->getActiveShiftByUserId($userId)) {
            return redirect()->route('combat_shifts.index')
                ->with('error', 'У вас вже є активне чергування.');
        }

        $this->combatShiftsAdminService->joinShift($id, $userId);

        return redirect()->route('combat_shifts.index')
            ->with('success', 'Ви приєдналися до чергування');
    }

    public function leave(int $id)
    {
        $userId = \Illuminate\Support\Facades\Auth::id();
        $this->combatShiftsAdminService->leaveShift($id, $userId);

        return redirect()->route('combat_shifts.index')
            ->with('success', 'Ви покинули чергування');
    }

    public function finish(int $id)
    {
        $this->combatShiftsAdminService->finishShift($id);

        return redirect()->route('combat_shifts.show', $id)
            ->with('success', 'Чергування успішно завершено');
    }

    public function reopen(int $id)
    {
        $this->combatShiftsAdminService->reopenShift($id);

        return redirect()->route('combat_shifts.show', $id)
            ->with('success', 'Чергування успішно відновлено');
    }
}
