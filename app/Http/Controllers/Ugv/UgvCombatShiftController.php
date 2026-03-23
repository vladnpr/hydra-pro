<?php

namespace App\Http\Controllers\Ugv;

use App\DTOs\CreateCombatShiftDTO;
use App\DTOs\UpdateCombatShiftDTO;
use App\Enums\PositionTypesEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\UgvCombatShiftStoreRequest;
use App\Http\Requests\UgvCombatShiftUpdateRequest;
use App\Repositories\Contracts\AmmunitionRepositoryInterface;
use App\Repositories\Contracts\PositionRepositoryInterface;
use App\Services\CombatShiftsAdminService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class UgvCombatShiftController extends Controller
{
    public function __construct(
        private readonly CombatShiftsAdminService      $combatShiftsAdminService,
        private readonly PositionRepositoryInterface   $positionRepository,
        private readonly AmmunitionRepositoryInterface $ammunitionRepository
    ) {
        $this->middleware(function ($request, $next) {
            $reportMethods = ['report', 'racesReport', 'activeFlightsReport', 'show'];
            $currentMethod = $request->route()->getActionMethod();

            if (in_array($currentMethod, $reportMethods)) {
                if (Gate::denies('view-reports') && Gate::denies('manage-ugv')) {
                    abort(403);
                }
            } else {
                if (Gate::denies('manage-ugv')) {
                    abort(403);
                }
            }

            return $next($request);
        });
    }

    public function index()
    {
        $shifts = $this->combatShiftsAdminService->getAllShifts(PositionTypesEnum::UGV->value);
        $userActiveShift = $this->combatShiftsAdminService->getActiveShiftByUserId(Auth::id());

        if ($userActiveShift && $userActiveShift->type !== PositionTypesEnum::UGV->value) {
            $userActiveShift = null;
        }

        return view('ugv.combat_shifts.index', compact('shifts', 'userActiveShift'));
    }

    public function create()
    {
        if ($activeShift = $this->combatShiftsAdminService->getActiveShiftByUserId(Auth::id())) {
             return redirect()->route('ugv.combat_shifts.index')
                ->with('error', 'У вас вже є відкрите чергування (тип: ' . $activeShift->type . '). Спочатку завершіть його.');
        }

        $users = \App\Models\User::all();
        $positions = $this->positionRepository->getActive(PositionTypesEnum::UGV->value);
        $ammunition = $this->ammunitionRepository->getActive(PositionTypesEnum::UGV->value);

        return view('ugv.combat_shifts.create', compact('positions', 'ammunition', 'users'));
    }

    public function store(UgvCombatShiftStoreRequest $request)
    {
        if ($this->combatShiftsAdminService->getActiveShiftByUserId(Auth::id())) {
            return redirect()->route('ugv.combat_shifts.index')
                ->with('error', 'У вас вже є відкрите чергування.');
        }

        try {
            $dto = CreateCombatShiftDTO::fromRequest($request);
            $this->combatShiftsAdminService->createShift($dto);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
             return redirect()->back()
                ->withErrors(['new_drones' => 'НРК з таким серійним номером вже існує в базі. Будь ласка, перевірте введені дані.'])
                ->withInput();
        }

        return redirect()->route('ugv.combat_shifts.index')
            ->with('success', 'Чергування успішно розпочато');
    }

    public function show(int $id)
    {
        $shift = $this->combatShiftsAdminService->getShiftById($id);
        if ($shift->type !== PositionTypesEnum::UGV->value) {
            abort(404);
        }
        $userActiveShift = $this->combatShiftsAdminService->getActiveShiftByUserId(Auth::id());
        return view('ugv.combat_shifts.show', compact('shift', 'userActiveShift'));
    }

    public function edit(int $id)
    {
        $shift = $this->combatShiftsAdminService->getShiftById($id);
        if ($shift->type !== PositionTypesEnum::UGV->value) {
            abort(404);
        }

        $users = \App\Models\User::all();
        $positions = $this->positionRepository->getActive(PositionTypesEnum::UGV->value);
        $ammunition = $this->ammunitionRepository->getActive(PositionTypesEnum::UGV->value);

        $currentAmmunition = [];
        foreach ($shift->ammunition as $a) {
            $currentAmmunition[$a['id']] = $a['quantity'];
        }

        return view('ugv.combat_shifts.edit', compact('shift', 'positions', 'ammunition', 'users', 'currentAmmunition'));
    }

    public function update(UgvCombatShiftUpdateRequest $request, int $id)
    {
        try {
            $dto = UpdateCombatShiftDTO::fromRequest($request);
            $this->combatShiftsAdminService->updateShift($id, $dto);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            return redirect()->back()
                ->withErrors(['new_drones' => 'НРК з таким серійним номером вже існує в базі. Будь ласка, перевірте введені дані.'])
                ->withInput();
        }

        return redirect()->route('ugv.combat_shifts.index')
            ->with('success', 'Чергування успішно оновлено');
    }

    public function destroy(int $id)
    {
        $this->combatShiftsAdminService->deleteShift($id);
        return redirect()->route('ugv.combat_shifts.index')
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
        return redirect()->route('ugv.combat_shifts.index')->with('success', 'Чергування завершено');
    }

    public function reopen(int $id)
    {
        $this->combatShiftsAdminService->reopenShift($id);
        return redirect()->back()->with('success', 'Чергування поновлено');
    }

    public function report(int $id)
    {
        $shift = $this->combatShiftsAdminService->getShiftById($id);
        return view('ugv.combat_shifts.report', compact('shift'));
    }

    public function spendingReport(int $id)
    {
        $shift = $this->combatShiftsAdminService->getShiftById($id);
        return view('ugv.combat_shifts.spending_report', compact('shift'));
    }

    public function racesReport(int $id, \Illuminate\Http\Request $request)
    {
        $shift = $this->combatShiftsAdminService->getShiftById($id);
        $date = $request->query('date', now()->format('Y-m-d'));

        $dayRaces = $shift->ugv_races[$date] ?? [];

        $workedRaces = collect($dayRaces)->where('result', 'worked');
        $notWorkedRaces = collect($dayRaces)->where('result', '!=', 'worked');

        return view('ugv.combat_shifts.races_report', compact('shift', 'date', 'workedRaces', 'notWorkedRaces'));
    }

    public function activeFlightsReport()
    {
        $shifts = $this->combatShiftsAdminService->getActiveShifts(PositionTypesEnum::UGV->value);
        return view('ugv.combat_shifts.active_races_report', compact('shifts'));
    }
}
