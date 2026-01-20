<?php

namespace App\Http\Controllers;

use App\Http\Requests\CombatShiftFlightStoreRequest;
use App\Http\Requests\CombatShiftFlightUpdateRequest;
use App\Models\CombatShiftFlight;
use App\Services\CombatShiftsAdminService;
use App\Repositories\Contracts\DroneRepositoryInterface;
use App\Repositories\Contracts\AmmunitionRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class FlightOperationsController extends Controller
{
    public function __construct(
        private readonly CombatShiftsAdminService $shiftService,
        private readonly DroneRepositoryInterface $droneRepository,
        private readonly AmmunitionRepositoryInterface $ammunitionRepository
    ) {}

    public function index()
    {
        $activeShift = $this->shiftService->getActiveShiftByUserId(Auth::id());

        if (!$activeShift) {
            return view('admin.flight_operations.no_active_shift');
        }

        $drones = $this->droneRepository->getActive();
        $ammunition = $this->ammunitionRepository->getActive();

        return view('admin.flight_operations.index', compact('activeShift', 'drones', 'ammunition'));
    }

    public function store(CombatShiftFlightStoreRequest $request): RedirectResponse
    {
        CombatShiftFlight::create($request->validated());

        return redirect()->route('flight_operations.index')
            ->with('success', 'Виліт успішно додано');
    }

    public function edit(int $id)
    {
        $flight = CombatShiftFlight::findOrFail($id);
        $activeShift = $this->shiftService->getActiveShiftByUserId(Auth::id());

        if (!$activeShift || $flight->combat_shift_id !== $activeShift->id) {
            return redirect()->route('flight_operations.index')
                ->with('error', 'Ви можете редагувати вильоти лише своєї активної зміни');
        }

        $drones = $this->droneRepository->getActive();
        $ammunition = $this->ammunitionRepository->getActive();

        return view('admin.flight_operations.edit', compact('flight', 'activeShift', 'drones', 'ammunition'));
    }

    public function update(CombatShiftFlightUpdateRequest $request, int $id): RedirectResponse
    {
        $flight = CombatShiftFlight::findOrFail($id);
        $activeShift = $this->shiftService->getActiveShiftByUserId(Auth::id());

        if (!$activeShift || $flight->combat_shift_id !== $activeShift->id) {
            return redirect()->route('flight_operations.index')
                ->with('error', 'Ви можете оновлювати вильоти лише своєї активної зміни');
        }

        $flight->update($request->validated());

        return redirect()->route('flight_operations.index')
            ->with('success', 'Виліт успішно оновлено');
    }

    public function destroy(int $id): RedirectResponse
    {
        $flight = CombatShiftFlight::findOrFail($id);
        $activeShift = $this->shiftService->getActiveShiftByUserId(Auth::id());

        if (!$activeShift || $flight->combat_shift_id !== $activeShift->id) {
            return redirect()->route('flight_operations.index')
                ->with('error', 'Ви можете видаляти вильоти лише своєї активної зміни');
        }

        $flight->delete();

        return redirect()->route('flight_operations.index')
            ->with('success', 'Виліт успішно видалено');
    }
}
