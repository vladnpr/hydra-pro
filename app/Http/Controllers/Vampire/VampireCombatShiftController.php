<?php

namespace App\Http\Controllers\Vampire;

use App\DTOs\CreateCombatShiftDTO;
use App\DTOs\UpdateCombatShiftDTO;
use App\Enums\PositionTypesEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\VampireCombatShiftStoreRequest;
use App\Http\Requests\VampireCombatShiftUpdateRequest;
use App\Repositories\Contracts\AmmunitionRepositoryInterface;
use App\Repositories\Contracts\PositionRepositoryInterface;
use App\Services\CombatShiftsAdminService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class VampireCombatShiftController extends Controller
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
                if (Gate::denies('view-reports') && Gate::denies('manage-vampire')) {
                    abort(403);
                }
            } else {
                if (Gate::denies('manage-vampire')) {
                    abort(403);
                }
            }

            return $next($request);
        });
    }

    public function index()
    {
        $shifts = $this->combatShiftsAdminService->getAllShifts(PositionTypesEnum::VAMPIRE->value);
        $userActiveShift = $this->combatShiftsAdminService->getActiveShiftByUserId(Auth::id());

        // Ensure userActiveShift is indeed a Vampire shift if we want to show it specifically
        if ($userActiveShift && $userActiveShift->type !== PositionTypesEnum::VAMPIRE->value) {
            $userActiveShift = null;
        }

        return view('vampire.combat_shifts.index', compact('shifts', 'userActiveShift'));
    }

    public function create()
    {
        if ($activeShift = $this->combatShiftsAdminService->getActiveShiftByUserId(Auth::id())) {
             return redirect()->route('vampire.combat_shifts.index')
                ->with('error', 'У вас вже є відкрите чергування (тип: ' . $activeShift->type . '). Спочатку завершіть його.');
        }

        $users = \App\Models\User::all();
        $positions = $this->positionRepository->getActive(PositionTypesEnum::VAMPIRE->value);
        $ammunition = $this->ammunitionRepository->getActive(PositionTypesEnum::VAMPIRE->value);

        return view('vampire.combat_shifts.create', compact('positions', 'ammunition', 'users'));
    }

    public function store(VampireCombatShiftStoreRequest $request)
    {
        if ($this->combatShiftsAdminService->getActiveShiftByUserId(Auth::id())) {
            return redirect()->route('vampire.combat_shifts.index')
                ->with('error', 'У вас вже є відкрите чергування.');
        }

        try {
            $dto = CreateCombatShiftDTO::fromRequest($request);
            $this->combatShiftsAdminService->createShift($dto);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
             return redirect()->back()
                ->withErrors(['new_drones' => 'Дрон з таким серійним номером вже існує в базі. Будь ласка, перевірте введені дані.'])
                ->withInput();
        }

        return redirect()->route('vampire.combat_shifts.index')
            ->with('success', 'Чергування успішно розпочато');
    }

    public function show(int $id)
    {
        $shift = $this->combatShiftsAdminService->getShiftById($id);
        if ($shift->type !== PositionTypesEnum::VAMPIRE->value) {
            abort(404);
        }
        $userActiveShift = $this->combatShiftsAdminService->getActiveShiftByUserId(Auth::id());
        return view('vampire.combat_shifts.show', compact('shift', 'userActiveShift'));
    }

    public function edit(int $id)
    {
        $shift = $this->combatShiftsAdminService->getShiftById($id);
        if ($shift->type !== PositionTypesEnum::VAMPIRE->value) {
            abort(404);
        }

        $users = \App\Models\User::all();
        $positions = $this->positionRepository->getActive(PositionTypesEnum::VAMPIRE->value);
        $ammunition = $this->ammunitionRepository->getActive(PositionTypesEnum::VAMPIRE->value);

        $currentAmmunition = [];
        foreach ($shift->ammunition as $a) {
            $currentAmmunition[$a['id']] = $a['quantity'];
        }

        return view('vampire.combat_shifts.edit', compact('shift', 'positions', 'ammunition', 'currentAmmunition', 'users'));
    }

    public function update(VampireCombatShiftUpdateRequest $request, int $id)
    {
        try {
            $dto = UpdateCombatShiftDTO::fromRequest($request);
            $this->combatShiftsAdminService->updateShift($id, $dto);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
             return redirect()->back()
                ->withErrors(['new_drones' => 'Дрон з таким серійним номером вже існує в базі. Будь ласка, перевірте введені дані.'])
                ->withInput();
        }

        return redirect()->route('vampire.combat_shifts.index')
            ->with('success', 'Чергування успішно оновлено');
    }

    public function destroy(int $id)
    {
        $shift = $this->combatShiftsAdminService->getShiftById($id);
        if ($shift->type !== PositionTypesEnum::VAMPIRE->value) {
            abort(404);
        }
        $this->combatShiftsAdminService->deleteShift($id);

        return redirect()->route('vampire.combat_shifts.index')
            ->with('success', 'Чергування успішно видалено');
    }

    public function join(int $id)
    {
        $userId = Auth::id();

        if ($this->combatShiftsAdminService->getActiveShiftByUserId($userId)) {
            return redirect()->route('vampire.combat_shifts.index')
                ->with('error', 'У вас вже є активне чергування.');
        }

        $this->combatShiftsAdminService->joinShift($id, $userId);

        return redirect()->route('vampire.combat_shifts.index')
            ->with('success', 'Ви приєдналися до чергування');
    }

    public function leave(int $id)
    {
        $userId = Auth::id();
        $this->combatShiftsAdminService->leaveShift($id, $userId);

        return redirect()->route('vampire.combat_shifts.index')
            ->with('success', 'Ви покинули чергування');
    }

    public function finish(int $id)
    {
        $this->combatShiftsAdminService->finishShift($id);

        return redirect()->route('vampire.combat_shifts.show', $id)
            ->with('success', 'Чергування успішно завершено');
    }

    public function flightsReport(int $id, \Illuminate\Http\Request $request)
    {
        $shift = $this->combatShiftsAdminService->getShiftById($id);
        if ($shift->type !== PositionTypesEnum::VAMPIRE->value) {
            abort(404);
        }
        $date = $request->query('date');

        if (!$date) {
            $now = now();
            if ($now->hour < 8) {
                $date = $now->copy()->subDay()->format('Y-m-d');
            } else {
                $date = $now->format('Y-m-d');
            }
        }

        // Отримуємо польоти за обрану дату
        $allFlights = $shift->vampire_flights[$date] ?? [];

        // Групуємо для звіту
        $workedFlights = array_filter($allFlights, fn($f) => $f['result'] === 'worked');
        $notWorkedFlights = array_filter($allFlights, fn($f) => $f['result'] !== 'worked');

        return view('vampire.combat_shifts.flights_report', compact('shift', 'date', 'workedFlights', 'notWorkedFlights'));
    }

    public function report(int $id, \Illuminate\Http\Request $request)
    {
        $shift = $this->combatShiftsAdminService->getShiftById($id);
        if ($shift->type !== PositionTypesEnum::VAMPIRE->value) {
            abort(404);
        }

        $hour = now()->hour;
        $defaultShiftType = ($hour >= 8 && $hour < 20) ? 'day' : 'night';
        $activeShiftType = $request->query('shift_type', $defaultShiftType);

        return view('vampire.combat_shifts.report', compact('shift', 'activeShiftType'));
    }

    public function spendingReport(int $id, \Illuminate\Http\Request $request)
    {
        $shift = $this->combatShiftsAdminService->getShiftById($id);
        if ($shift->type !== PositionTypesEnum::VAMPIRE->value) {
            abort(404);
        }

        $date = $request->query('date');
        if (!$date) {
            $now = now();
            if ($now->hour < 8) {
                $date = $now->copy()->subDay()->format('Y-m-d');
            } else {
                $date = $now->format('Y-m-d');
            }
        }

        $flights = $shift->vampire_flights[$date] ?? [];

        $spendingAmmunition = [];
        foreach ($flights as $flight) {
            // Збираємо витрати БК
            if (!empty($flight['ammunition'])) {
                foreach ($flight['ammunition'] as $ammo) {
                    $name = $ammo['name'];
                    $qty = $ammo['quantity'];
                    if (!isset($spendingAmmunition[$name])) {
                        $spendingAmmunition[$name] = 0;
                    }
                    $spendingAmmunition[$name] += $qty;
                }
            }
        }

        // Збираємо втрати дронів по таблиці vampire_drones
        $lostDrones = \App\Models\VampireDrone::where('position_id', $shift->position_id)
            ->where('status', 'lost')
            ->get()
            ->map(fn($d) => [
                'name' => $d->name,
                'serial' => $d->serial_number,
                'lost_at' => $d->lost_at ? $d->lost_at->format('d.m.Y') : '-'
            ])->toArray();

        return view('vampire.combat_shifts.spending_report', compact('shift', 'date', 'spendingAmmunition', 'lostDrones'));
    }

    public function activeFlightsReport(\Illuminate\Http\Request $request)
    {
        $activeShift = $this->combatShiftsAdminService->getActiveShiftByUserId(Auth::id());

        if (!$activeShift || $activeShift->type !== PositionTypesEnum::VAMPIRE->value) {
            return redirect()->route('vampire.combat_shifts.index')
                ->with('error', 'У вас немає активної зміни VAMPIRE.');
        }

        return $this->flightsReport($activeShift->id, $request);
    }

    public function reopen(int $id)
    {
        $this->combatShiftsAdminService->reopenShift($id);

        return redirect()->route('vampire.combat_shifts.show', $id)
            ->with('success', 'Чергування успішно відновлено');
    }
}
