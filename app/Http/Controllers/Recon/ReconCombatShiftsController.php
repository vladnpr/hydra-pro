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
            $reportMethods = ['report', 'flightsReport', 'activeFlightsReport', 'spendingReport', 'show'];
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
                ->withErrors(['new_drones' => 'Дрон з таким серійним номером вже існує в базі. Будь ласка, перевірте введені дані.'])
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
                ->withErrors(['new_drones' => 'Дрон з таким серійним номером вже існує в базі. Будь ласка, перевірте введені дані.'])
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

        $from = $request->query('from');
        $to = $request->query('to');

        if (!$from || !$to) {
            [$from, $to] = $this->combatShiftsAdminService->getDefaultReportRange();
        }

        $fromDate = \Carbon\Carbon::parse($from);
        $toDate = \Carbon\Carbon::parse($to);

        $allFlightsList = [];
        foreach ($shift->recon_flights as $dateFlights) {
            foreach ($dateFlights as $flight) {
                $allFlightsList[] = $flight;
            }
        }

        $filteredFlights = collect($allFlightsList)->filter(function ($flight) use ($fromDate, $toDate) {
            $flightTime = \Carbon\Carbon::parse($flight['flight_time']);
            return $flightTime->between($fromDate, $toDate);
        })->sortByDesc('flight_time');

        $dayFlights = $filteredFlights->filter(fn($f) => $f['shift_type'] === 'day')->values()->all();
        $nightFlights = $filteredFlights->filter(fn($f) => $f['shift_type'] === 'night')->values()->all();

        // Розрахунок витрат
        $spendingAmmunition = [];
        $strikeCoordinates = [];
        $totalFlights = $filteredFlights->count();
        $combatFlights = 0;
        $logisticsFlights = 0;

        foreach ($filteredFlights as $flight) {
            $mission = $flight['mission_type'] ?? '';
            if (in_array($mission, ['combat', 'patrol'])) {
                $combatFlights++;
            } elseif ($mission === 'logistics') {
                $logisticsFlights++;
            }

            if (!empty($flight['ammunition'])) {
                foreach ($flight['ammunition'] as $ammo) {
                    $name = $ammo['name'];
                    $qty = $ammo['quantity'];
                    $spendingAmmunition[$name] = ($spendingAmmunition[$name] ?? 0) + $qty;
                }
            }

            // Координати для ударних вильотів та патрулів
            if (in_array(($flight['mission_type'] ?? ''), ['combat', 'patrol']) && !empty($flight['coordinates'])) {
                $strikeCoordinates[] = $flight['coordinates'];
            }
        }

        $strikeCoordinates = array_unique($strikeCoordinates);

        $lostDrones = \App\Models\ReconDrone::where('position_id', $shift->position_id)
            ->where('status', 'lost')
            ->whereBetween('updated_at', [$fromDate, $toDate])
            ->get()
            ->map(fn($d) => [
                'name' => $d->name,
                'serial' => $d->serial_number,
                'lost_at' => $d->updated_at ? $d->updated_at->format('d.m.y H:i') : '-'
            ])->toArray();

        return view('recon.combat_shifts.flights_report', compact(
            'shift', 'from', 'to', 'dayFlights', 'nightFlights', 'spendingAmmunition',
            'lostDrones', 'strikeCoordinates', 'totalFlights', 'combatFlights', 'logisticsFlights'
        ));
    }

    public function report(int $id, \Illuminate\Http\Request $request)
    {
        $shift = $this->combatShiftsAdminService->getShiftById($id);
        if ($shift->type !== PositionTypesEnum::RECON->value) {
            abort(404);
        }

        return view('recon.combat_shifts.report', compact('shift'));
    }

    public function spendingReport(int $id, \Illuminate\Http\Request $request)
    {
        $shift = $this->combatShiftsAdminService->getShiftById($id);
        if ($shift->type !== PositionTypesEnum::RECON->value) {
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

        $flights = $shift->recon_flights[$date] ?? [];

        $spendingAmmunition = [];
        foreach ($flights as $flight) {
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

        $startDate = \Carbon\Carbon::parse($date)->hour(8)->minute(0)->second(0);
        $endDate = $startDate->copy()->addDay();

        $lostDrones = \App\Models\ReconDrone::where('position_id', $shift->position_id)
            ->where('status', 'lost')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->get()
            ->map(fn($d) => [
                'name' => $d->name,
                'serial' => $d->serial_number,
                'lost_at' => $d->updated_at ? $d->updated_at->format('H:i') : '-'
            ])->toArray();

        return view('recon.combat_shifts.spending_report', compact('shift', 'date', 'spendingAmmunition', 'lostDrones'));
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
