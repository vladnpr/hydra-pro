<?php

namespace App\Http\Controllers;

use App\Models\AirDefenceFlight;
use App\Models\AirDefenceDrone;
use App\Models\AirDefenceAmmunition;
use App\Models\Position;
use App\Enums\PositionTypesEnum;
use App\Http\Requests\AirDefenceFlightStoreRequest;
use App\Services\CombatShiftsAdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AirDefenceFlightsController extends Controller
{
    public function __construct(private readonly CombatShiftsAdminService $shiftService)
    {
        $this->middleware(function ($request, $next) {
            if (Gate::denies('manage-air-defence')) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function downloadVideo(int $id): StreamedResponse|RedirectResponse
    {
        $flight = AirDefenceFlight::findOrFail($id);

        if (!$flight->video_path || !Storage::disk('public')->exists($flight->video_path)) {
            return redirect()->back()->with('error', 'Відео не знайдено');
        }

        return Storage::disk('public')->download($flight->video_path);
    }

    public function index()
    {
        $userActiveShift = $this->shiftService->getActiveShiftByUserId(Auth::id());

        if (!$userActiveShift || $userActiveShift->type !== PositionTypesEnum::AIR_DEFENCE->value) {
            return view('admin.air_defence.flights.no_active_shift');
        }

        $flights = AirDefenceFlight::with(['drone', 'ammunition', 'position'])
            ->where('position_id', $userActiveShift->position_id)
            ->orderBy('start_time', 'desc')
            ->get()
            ->groupBy(function ($f) {
                return $f->start_time ? $f->start_time->format('Y-m-d') : $f->created_at->format('Y-m-d');
            });

        $drones = AirDefenceDrone::where('status', 'active')->get();
        $ammunition = AirDefenceAmmunition::where('status', 'active')->get();

        return view('admin.air_defence.flights.index', compact('userActiveShift', 'flights', 'drones', 'ammunition'));
    }

    public function create()
    {
        $positions = Position::where('type', PositionTypesEnum::AIR_DEFENCE->value)->get();
        $drones = AirDefenceDrone::where('status', 'active')->get();
        $ammunition = AirDefenceAmmunition::where('status', 'active')->get();
        return view('admin.air_defence.flights.create', compact('positions', 'drones', 'ammunition'));
    }

    public function store(AirDefenceFlightStoreRequest $request)
    {
        $data = $request->validated();
        $data['detonation'] = (bool)$request->detonation;
        $data['stream'] = $request->has('stream_switch') ? '+' : '-';

        if ($request->hasFile('video')) {
            $data['video_path'] = $request->file('video')->store('air-defence/flights/videos', 'public');
        }

        $flight = AirDefenceFlight::create($data);

        // Update quantities in active shift
        $userActiveShift = $this->shiftService->getActiveShiftByUserId(Auth::id());
        $result = mb_strtolower($data['result']);
        $isExpense = str_contains($result, 'втрата') ||
            str_contains($result, 'ціл') ||
            str_contains($result, 'влучання') ||
            str_contains($result, 'збито') ||
            str_contains($result, 'знищено');

        if ($userActiveShift && $userActiveShift->id && $isExpense) {
            $this->shiftService->updateDroneQuantity($userActiveShift->id, $data['air_defence_drone_id'], -1);
            $this->shiftService->updateAmmunitionQuantity($userActiveShift->id, $data['air_defence_ammunition_id'], -1);
        }

        return redirect()->route('air-defence.flights.index')
            ->with('success', 'Виліт ППО успішно додано');
    }

    public function edit(int $id)
    {
        $flight = AirDefenceFlight::findOrFail($id);
        $positions = Position::where('type', PositionTypesEnum::AIR_DEFENCE->value)->get();
        $drones = AirDefenceDrone::all();
        $ammunition = AirDefenceAmmunition::all();
        return view('admin.air_defence.flights.edit', compact('flight', 'positions', 'drones', 'ammunition'));
    }

    public function update(AirDefenceFlightStoreRequest $request, int $id)
    {
        $flight = AirDefenceFlight::findOrFail($id);
        $data = $request->validated();
        $data['detonation'] = (bool)$request->detonation;
        $data['stream'] = $request->has('stream_switch') ? '+' : '-';

        $oldDroneId = $flight->air_defence_drone_id;
        $oldAmmoId = $flight->air_defence_ammunition_id;
        $oldResult = $flight->result;

        if ($request->hasFile('video')) {
            if ($flight->video_path) {
                Storage::disk('public')->delete($flight->video_path);
            }
            $data['video_path'] = $request->file('video')->store('air-defence/flights/videos', 'public');
        }

        $flight->update($data);

        // Update quantities in active shift
        $userActiveShift = $this->shiftService->getActiveShiftByUserId(Auth::id());
        if ($userActiveShift && $userActiveShift->id) {
            $newResult = mb_strtolower($data['result']);
            $oldResult = mb_strtolower($flight->result);

            $isNewExpense = str_contains($newResult, 'втрата') ||
                str_contains($newResult, 'ціл') ||
                str_contains($newResult, 'влучання') ||
                str_contains($newResult, 'збито') ||
                str_contains($newResult, 'знищено');

            $isOldExpense = str_contains($oldResult, 'втрата') ||
                str_contains($oldResult, 'ціл') ||
                str_contains($oldResult, 'влучання') ||
                str_contains($oldResult, 'збито') ||
                str_contains($oldResult, 'знищено');

            // 1. Якщо раніше НЕ була витрата, а стала витратою - треба відняти дрон і БК
            if (!$isOldExpense && $isNewExpense) {
                $this->shiftService->updateDroneQuantity($userActiveShift->id, $data['air_defence_drone_id'], -1);
                $this->shiftService->updateAmmunitionQuantity($userActiveShift->id, $data['air_defence_ammunition_id'], -1);
            }
            // 2. Якщо раніше БУЛА витрата, а тепер НЕ витрата - треба повернути дрон і БК (старі)
            elseif ($isOldExpense && !$isNewExpense) {
                $this->shiftService->updateDroneQuantity($userActiveShift->id, $oldDroneId, 1);
                $this->shiftService->updateAmmunitionQuantity($userActiveShift->id, $oldAmmoId, 1);
            }
            // 3. Якщо в обох випадках витрата, обробляємо зміну дрона/БК
            elseif ($isOldExpense && $isNewExpense) {
                if ($oldDroneId != $data['air_defence_drone_id']) {
                    $this->shiftService->updateDroneQuantity($userActiveShift->id, $oldDroneId, 1);
                    $this->shiftService->updateDroneQuantity($userActiveShift->id, $data['air_defence_drone_id'], -1);
                }
                if ($oldAmmoId != $data['air_defence_ammunition_id']) {
                    $this->shiftService->updateAmmunitionQuantity($userActiveShift->id, $oldAmmoId, 1);
                    $this->shiftService->updateAmmunitionQuantity($userActiveShift->id, $data['air_defence_ammunition_id'], -1);
                }
            }
        }

        return redirect()->route('air-defence.flights.index')
            ->with('success', 'Виліт ППО успішно оновлено');
    }

    public function destroy(int $id)
    {
        $flight = AirDefenceFlight::findOrFail($id);

        $userActiveShift = $this->shiftService->getActiveShiftByUserId(Auth::id());
        $result = mb_strtolower($flight->result);
        $isExpense = str_contains($result, 'втрата') ||
            str_contains($result, 'ціл') ||
            str_contains($result, 'влучання') ||
            str_contains($result, 'збито') ||
            str_contains($result, 'знищено');

        if ($userActiveShift && $userActiveShift->id && $isExpense) {
            $this->shiftService->updateDroneQuantity($userActiveShift->id, $flight->air_defence_drone_id, 1);
            $this->shiftService->updateAmmunitionQuantity($userActiveShift->id, $flight->air_defence_ammunition_id, 1);
        }

        if ($flight->video_path) {
            Storage::disk('public')->delete($flight->video_path);
        }

        $flight->delete();

        return redirect()->route('air-defence.flights.index')
            ->with('success', 'Виліт ППО успішно видалено');
    }
}
