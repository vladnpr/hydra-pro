<?php

namespace App\Http\Controllers;

use App\DTOs\CreateCombatShiftDTO;
use App\DTOs\UpdateCombatShiftDTO;
use App\Http\Requests\CombatShiftStoreRequest;
use App\Http\Requests\CombatShiftUpdateRequest;
use App\Services\CombatShiftsAdminService;
use App\Repositories\Contracts\PositionRepositoryInterface;
use App\Repositories\Contracts\DroneRepositoryInterface;
use App\Repositories\Contracts\AmmunitionRepositoryInterface;

class CombatShiftsController extends Controller
{
    public function __construct(
        private readonly CombatShiftsAdminService $service,
        private readonly PositionRepositoryInterface $positionRepository,
        private readonly DroneRepositoryInterface $droneRepository,
        private readonly AmmunitionRepositoryInterface $ammunitionRepository
    ) {}

    public function index()
    {
        $shifts = $this->service->getAllShifts();
        $activeShift = $this->service->getActiveShiftByUserId(\Illuminate\Support\Facades\Auth::id());
        return view('admin.combat_shifts.index', compact('shifts', 'activeShift'));
    }

    public function create()
    {
        if ($this->service->getActiveShiftByUserId(\Illuminate\Support\Facades\Auth::id())) {
            return redirect()->route('combat_shifts.index')
                ->with('error', 'У вас вже є відкрите чергування. Спочатку завершіть його.');
        }

        $users = \App\Models\User::all();
        $positions = $this->positionRepository->getActive();
        $drones = $this->droneRepository->getActive();
        $ammunition = $this->ammunitionRepository->getActive();
        return view('admin.combat_shifts.create', compact('positions', 'drones', 'ammunition', 'users'));
    }

    public function store(CombatShiftStoreRequest $request)
    {
        if ($this->service->getActiveShiftByUserId(\Illuminate\Support\Facades\Auth::id())) {
            return redirect()->route('combat_shifts.index')
                ->with('error', 'У вас вже є відкрите чергування.');
        }

        $dto = CreateCombatShiftDTO::fromRequest($request);
        $this->service->createShift($dto);

        return redirect()->route('combat_shifts.index')
            ->with('success', 'Чергування успішно розпочато');
    }

    public function show(int $id)
    {
        $shift = $this->service->getShiftById($id);
        return view('admin.combat_shifts.show', compact('shift'));
    }

    public function report(int $id)
    {
        $shift = $this->service->getShiftById($id);

        // Номер дня вираховується від дня старту зміни
        // Перший день зміни - це День 1
        $shiftDate = \Carbon\Carbon::parse($shift->started_at);
        $now = \Carbon\Carbon::now();
        $dayNumber = (int) $shiftDate->diffInDays($now) + 1;

        return view('admin.combat_shifts.report', compact('shift', 'dayNumber'));
    }

    public function flightsReport(int $id, \Illuminate\Http\Request $request)
    {
        $shift = $this->service->getShiftById($id);
        $date = $request->query('date', now()->format('Y-m-d'));

        // Отримуємо польоти за обрану дату
        $flights = $shift->flights[$date] ?? [];

        return view('admin.combat_shifts.flights_report', compact('shift', 'date', 'flights'));
    }

    public function activeFlightsReport(\Illuminate\Http\Request $request)
    {
        $activeShift = $this->service->getActiveShiftByUserId(\Illuminate\Support\Facades\Auth::id());

        if (!$activeShift) {
            return redirect()->route('flight_operations.index')
                ->with('error', 'У вас немає активної зміни.');
        }

        return $this->flightsReport($activeShift->id, $request);
    }

    public function activeRemainsReport()
    {
        $activeShift = $this->service->getActiveShiftByUserId(\Illuminate\Support\Facades\Auth::id());

        if (!$activeShift) {
            return redirect()->route('flight_operations.index')
                ->with('error', 'У вас немає активної зміни.');
        }

        return $this->report($activeShift->id);
    }

    public function edit(int $id)
    {
        $shift = $this->service->getShiftById($id);
        $users = \App\Models\User::all();
        $positions = $this->positionRepository->getActive();
        $drones = $this->droneRepository->getActive();
        $ammunition = $this->ammunitionRepository->getActive();

        // Prepare current quantities for the form
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
        $this->service->updateShift($id, $dto);

        return redirect()->route('combat_shifts.index')
            ->with('success', 'Чергування успішно оновлено');
    }

    public function destroy(int $id)
    {
        $this->service->deleteShift($id);

        return redirect()->route('combat_shifts.index')
            ->with('success', 'Чергування успішно видалено');
    }

    public function join(int $id)
    {
        $userId = \Illuminate\Support\Facades\Auth::id();

        if ($this->service->getActiveShiftByUserId($userId)) {
            return redirect()->route('combat_shifts.index')
                ->with('error', 'У вас вже є активне чергування.');
        }

        $this->service->joinShift($id, $userId);

        return redirect()->route('combat_shifts.index')
            ->with('success', 'Ви приєдналися до чергування');
    }

    public function leave(int $id)
    {
        $userId = \Illuminate\Support\Facades\Auth::id();
        $this->service->leaveShift($id, $userId);

        return redirect()->route('combat_shifts.index')
            ->with('success', 'Ви покинули чергування');
    }

    public function finish(int $id)
    {
        $this->service->finishShift($id);

        return redirect()->route('combat_shifts.show', $id)
            ->with('success', 'Чергування успішно завершено');
    }

    public function reopen(int $id)
    {
        $this->service->reopenShift($id);

        return redirect()->route('combat_shifts.show', $id)
            ->with('success', 'Чергування успішно відновлено');
    }
}
