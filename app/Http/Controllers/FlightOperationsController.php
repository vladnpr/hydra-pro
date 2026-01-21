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
use Illuminate\Support\Facades\Storage;

class FlightOperationsController extends Controller
{
    public function __construct(
        private readonly CombatShiftsAdminService $shiftService,
        private readonly DroneRepositoryInterface $droneRepository,
        private readonly AmmunitionRepositoryInterface $ammunitionRepository
    ) {}

    public function index()
    {
        $userActiveShift = $this->shiftService->getActiveShiftByUserId(Auth::id());

        if (!$userActiveShift) {
            return view('admin.flight_operations.no_active_shift');
        }

        $drones = $this->droneRepository->getActive();
        $ammunition = $this->ammunitionRepository->getActive();

        return view('admin.flight_operations.index', compact('userActiveShift', 'drones', 'ammunition'));
    }

    public function store(CombatShiftFlightStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('video')) {
            $data['video_path'] = $request->file('video')->store('flights/videos', 'public');
        }

        CombatShiftFlight::create($data);

        return redirect()->route('flight_operations.index')
            ->with('success', 'Виліт успішно додано');
    }

    public function edit(int $id)
    {
        $flight = CombatShiftFlight::findOrFail($id);
        $userActiveShift = $this->shiftService->getActiveShiftByUserId(Auth::id());

        if (!$userActiveShift || $flight->combat_shift_id !== $userActiveShift->id) {
            return redirect()->route('flight_operations.index')
                ->with('error', 'Ви можете редагувати вильоти лише своєї активної зміни');
        }

        $drones = $this->droneRepository->getActive();
        $ammunition = $this->ammunitionRepository->getActive();

        return view('admin.flight_operations.edit', compact('flight', 'userActiveShift', 'drones', 'ammunition'));
    }

    public function update(CombatShiftFlightUpdateRequest $request, int $id): RedirectResponse
    {
        $flight = CombatShiftFlight::findOrFail($id);
        $userActiveShift = $this->shiftService->getActiveShiftByUserId(Auth::id());

        if (!$userActiveShift || $flight->combat_shift_id !== $userActiveShift->id) {
            return redirect()->route('flight_operations.index')
                ->with('error', 'Ви можете оновлювати вильоти лише своєї активної зміни');
        }

        $data = $request->validated();

        if ($request->hasFile('video')) {
            if ($flight->video_path) {
                Storage::disk('public')->delete($flight->video_path);
            }
            $data['video_path'] = $request->file('video')->store('flights/videos', 'public');
        }

        $flight->update($data);

        return redirect()->route('flight_operations.index')
            ->with('success', 'Виліт успішно оновлено');
    }

    public function destroy(int $id): RedirectResponse
    {
        $flight = CombatShiftFlight::findOrFail($id);
        $userActiveShift = $this->shiftService->getActiveShiftByUserId(Auth::id());

        if (!$userActiveShift || $flight->combat_shift_id !== $userActiveShift->id) {
            return redirect()->route('flight_operations.index')
                ->with('error', 'Ви можете видаляти вильоти лише своєї активної зміни');
        }

        if ($flight->video_path) {
            Storage::disk('public')->delete($flight->video_path);
        }

        $flight->delete();

        return redirect()->route('flight_operations.index')
            ->with('success', 'Виліт успішно видалено');
    }
}
