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
            if (Gate::denies('manage-recon')) {
                abort(403);
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

        $dto = CreateCombatShiftDTO::fromRequest($request);
        $this->combatShiftsAdminService->createShift($dto);

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
        $dto = UpdateCombatShiftDTO::fromRequest($request);
        $this->combatShiftsAdminService->updateShift($id, $dto);

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

    public function reopen(int $id)
    {
        $this->combatShiftsAdminService->reopenShift($id);

        return redirect()->route('recon.combat_shifts.show', $id)
            ->with('success', 'Чергування успішно відновлено');
    }
}
