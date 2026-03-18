<?php

namespace App\Http\Controllers\Vampire;

use App\Enums\PositionTypesEnum;
use App\Http\Controllers\Controller;
use App\Services\CombatShiftsAdminService;
use Illuminate\Support\Facades\Auth;

class VampireCombatShiftController extends Controller
{
    public function __construct(
        private readonly CombatShiftsAdminService $combatShiftsAdminService
    ) {}

    public function index() {
        $shifts = $this->combatShiftsAdminService->getAllShifts(PositionTypesEnum::VAMPIRE->value);
        $userActiveShift = $this->combatShiftsAdminService->getActiveShiftByUserId(Auth::id());

        // Ensure userActiveShift is indeed a Recon shift if we want to show it specifically
        if ($userActiveShift && $userActiveShift->type !== PositionTypesEnum::RECON->value) {
            $userActiveShift = null;
        }

        return view('vampire.combat_shifts.index', compact('shifts', 'userActiveShift'));
    }
}
