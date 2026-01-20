<?php

namespace App\Http\Controllers;

use App\Models\CombatShiftFlight;
use App\Http\Requests\CombatShiftFlightUpdateRequest;
use App\Repositories\Contracts\DroneRepositoryInterface;
use App\Repositories\Contracts\AmmunitionRepositoryInterface;
use Illuminate\Http\RedirectResponse;

class CombatShiftFlightsController extends Controller
{
    public function __construct(
        private readonly DroneRepositoryInterface $droneRepository,
        private readonly AmmunitionRepositoryInterface $ammunitionRepository
    ) {}

    public function edit(int $id)
    {
        $flight = CombatShiftFlight::with('combatShift')->findOrFail($id);
        $drones = $this->droneRepository->getActive();
        $ammunition = $this->ammunitionRepository->getActive();

        return view('admin.combat_shifts.flights.edit', compact('flight', 'drones', 'ammunition'));
    }

    public function update(CombatShiftFlightUpdateRequest $request, int $id): RedirectResponse
    {
        $flight = CombatShiftFlight::findOrFail($id);
        $flight->update($request->validated());

        return redirect()->route('combat_shifts.show', $flight->combat_shift_id)
            ->with('success', 'Виліт успішно оновлено');
    }

    public function destroy(int $id): RedirectResponse
    {
        $flight = CombatShiftFlight::findOrFail($id);
        $shiftId = $flight->combat_shift_id;
        $flight->delete();

        return redirect()->route('combat_shifts.show', $shiftId)
            ->with('success', 'Виліт успішно видалено');
    }
}
