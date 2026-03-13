<?php

namespace App\Http\Controllers\Recon;

use App\Http\Controllers\Controller;
use App\DTOs\CreateCombatShiftDTO;
use App\DTOs\UpdateCombatShiftDTO;
use App\Http\Requests\ReconCombatShiftStoreRequest;
use App\Http\Requests\ReconCombatShiftUpdateRequest;
use App\Services\CombatShiftsAdminService;
use App\Repositories\Contracts\PositionRepositoryInterface;
use App\Repositories\Contracts\AmmunitionRepositoryInterface;
use App\Enums\PositionTypesEnum;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class ReconCombatShiftsController extends Controller
{
    public function __construct(
        private readonly CombatShiftsAdminService      $combatShiftsAdminService,
        private readonly PositionRepositoryInterface   $positionRepository,
        private readonly AmmunitionRepositoryInterface $ammunitionRepository
    ) {
        $this->middleware(function ($request, $next) {
            $reportMethods = ['report', 'flightsReport', 'activeFlightsReport', 'show'];
            $currentMethod = $request->route()->getActionMethod();

            if (in_array($currentMethod, $reportMethods)) {
                if (Gate::denies('view-reports') && Gate::denies('manage-recon')) {
                    abort(403);
                }
            } else {
                if (Gate::denies('manage-recon')) {
                    abort(403);
                }
            }

            return $next($request);
        });
    }

    public function index()
    {
        $shifts = $this->combatShiftsAdminService->getAllShifts(PositionTypesEnum::RECON->value);
        $userActiveShift = $this->combatShiftsAdminService->getActiveShiftByUserId(Auth::id());

        // Ensure userActiveShift is indeed a Recon shift if we want to show it specifically
        if ($userActiveShift && $userActiveShift->type !== PositionTypesEnum::RECON->value) {
            $userActiveShift = null;
        }

        return view('recon.combat_shifts.index', compact('shifts', 'userActiveShift'));
    }

    public function create()
    {
        if ($activeShift = $this->combatShiftsAdminService->getActiveShiftByUserId(Auth::id())) {
             return redirect()->route('recon.combat_shifts.index')
                ->with('error', 'У вас вже є відкрите чергування (тип: ' . $activeShift->type . '). Спочатку завершіть його.');
        }

        $users = \App\Models\User::all();
        $positions = $this->positionRepository->getActive(PositionTypesEnum::RECON->value);
        $ammunition = $this->ammunitionRepository->getActive(PositionTypesEnum::RECON->value);

        return view('recon.combat_shifts.create', compact('positions', 'ammunition', 'users'));
    }

    public function store(ReconCombatShiftStoreRequest $request)
    {
        if ($this->combatShiftsAdminService->getActiveShiftByUserId(Auth::id())) {
            return redirect()->route('recon.combat_shifts.index')
                ->with('error', 'У вас вже є відкрите чергування.');
        }

        try {
            $dto = CreateCombatShiftDTO::fromRequest($request);
            $this->combatShiftsAdminService->createShift($dto);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
             return redirect()->back()
                ->withErrors(['new_recon_drones' => 'Дрон з таким серійним номером вже існує в базі. Будь ласка, перевірте введені дані.'])
                ->withInput();
        }

        return redirect()->route('recon.combat_shifts.index')
            ->with('success', 'Чергування успішно розпочато');
    }

    public function show(int $id)
    {
        $shift = $this->combatShiftsAdminService->getShiftById($id);
        if ($shift->type !== PositionTypesEnum::RECON->value) {
            abort(404);
        }
        $userActiveShift = $this->combatShiftsAdminService->getActiveShiftByUserId(Auth::id());
        return view('recon.combat_shifts.show', compact('shift', 'userActiveShift'));
    }

    public function edit(int $id)
    {
        $shift = $this->combatShiftsAdminService->getShiftById($id);
        if ($shift->type !== PositionTypesEnum::RECON->value) {
            abort(404);
        }

        $users = \App\Models\User::all();
        $positions = $this->positionRepository->getActive(PositionTypesEnum::RECON->value);
        $ammunition = $this->ammunitionRepository->getActive(PositionTypesEnum::RECON->value);

        $currentAmmunition = [];
        foreach ($shift->ammunition as $a) {
            $currentAmmunition[$a['id']] = $a['quantity'];
        }

        return view('recon.combat_shifts.edit', compact('shift', 'positions', 'ammunition', 'currentAmmunition', 'users'));
    }

    public function update(ReconCombatShiftUpdateRequest $request, int $id)
    {
        try {
            $dto = UpdateCombatShiftDTO::fromRequest($request);
            $this->combatShiftsAdminService->updateShift($id, $dto);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
             return redirect()->back()
                ->withErrors(['new_recon_drones' => 'Дрон з таким серійним номером вже існує в базі. Будь ласка, перевірте введені дані.'])
                ->withInput();
        }

        return redirect()->route('recon.combat_shifts.index')
            ->with('success', 'Чергування успішно оновлено');
    }

    public function destroy(int $id)
    {
        $shift = $this->combatShiftsAdminService->getShiftById($id);
        if ($shift->type !== PositionTypesEnum::RECON->value) {
            abort(404);
        }
        $this->combatShiftsAdminService->deleteShift($id);

        return redirect()->route('recon.combat_shifts.index')
            ->with('success', 'Чергування успішно видалено');
    }

    public function join(int $id)
    {
        $userId = Auth::id();

        if ($this->combatShiftsAdminService->getActiveShiftByUserId($userId)) {
            return redirect()->route('recon.combat_shifts.index')
                ->with('error', 'У вас вже є активне чергування.');
        }

        $this->combatShiftsAdminService->joinShift($id, $userId);

        return redirect()->route('recon.combat_shifts.index')
            ->with('success', 'Ви приєдналися до чергування');
    }

    public function leave(int $id)
    {
        $userId = Auth::id();
        $this->combatShiftsAdminService->leaveShift($id, $userId);

        return redirect()->route('recon.combat_shifts.index')
            ->with('success', 'Ви покинули чергування');
    }

    public function finish(int $id)
    {
        $this->combatShiftsAdminService->finishShift($id);

        return redirect()->route('recon.combat_shifts.show', $id)
            ->with('success', 'Чергування успішно завершено');
    }

    public function flightsReport(int $id, \Illuminate\Http\Request $request)
    {
        $shift = $this->combatShiftsAdminService->getShiftById($id);
        if ($shift->type !== PositionTypesEnum::RECON->value) {
            abort(404);
        }
        $date = $request->query('date', now()->format('Y-m-d'));
        $activeShiftType = $request->query('shift_type', 'day');

        // Отримуємо польоти за обрану дату
        // Завдяки зміні в CombatShiftDTO, у recon_flights[date] вже лежать польоти,
        // які відбулися з 08:00 обраного дня до 08:00 наступного дня (якщо вони night)
        $allFlights = $shift->recon_flights[$date] ?? [];

        // Фільтруємо за зміною
        $dayFlights = array_filter($allFlights, fn($f) => $f['shift_type'] === 'day');
        $nightFlights = array_filter($allFlights, fn($f) => $f['shift_type'] === 'night');

        return view('recon.combat_shifts.flights_report', compact('shift', 'date', 'dayFlights', 'nightFlights', 'activeShiftType'));
    }

    public function report(int $id, \Illuminate\Http\Request $request)
    {
        $shift = $this->combatShiftsAdminService->getShiftById($id);
        if ($shift->type !== PositionTypesEnum::RECON->value) {
            abort(404);
        }

        $hour = now()->hour;
        $defaultShiftType = ($hour >= 8 && $hour < 20) ? 'day' : 'night';
        $activeShiftType = $request->query('shift_type', $defaultShiftType);

        return view('recon.combat_shifts.report', compact('shift', 'activeShiftType'));
    }

    public function activeFlightsReport(\Illuminate\Http\Request $request)
    {
        $activeShift = $this->combatShiftsAdminService->getActiveShiftByUserId(Auth::id());

        if (!$activeShift || $activeShift->type !== PositionTypesEnum::RECON->value) {
            return redirect()->route('recon.flights.index')
                ->with('error', 'У вас немає активної зміни RECON.');
        }

        return $this->flightsReport($activeShift->id, $request);
    }

    public function reopen(int $id)
    {
        $this->combatShiftsAdminService->reopenShift($id);

        return redirect()->route('recon.combat_shifts.show', $id)
            ->with('success', 'Чергування успішно відновлено');
    }
}
