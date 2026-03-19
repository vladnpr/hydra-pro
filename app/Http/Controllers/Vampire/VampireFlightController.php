<?php

namespace App\Http\Controllers\Vampire;

use App\Http\Controllers\Controller;
use App\Enums\ShiftTypeEnum;
use App\Models\VampireFlight;
use App\Models\VampireFlightPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\JsonResponse;

class VampireFlightController extends Controller
{
    public function __construct(private readonly \App\Services\CombatShiftsAdminService $shiftService)
    {
        $this->middleware(function ($request, $next) {
            if (Gate::denies('manage-vampire')) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function setShiftType(Request $request): JsonResponse
    {
        $request->validate([
            'shift_type' => 'required|string|in:day,night'
        ]);

        session(['vampire_active_shift_type' => $request->shift_type]);

        return response()->json(['success' => true]);
    }

    public function index()
    {
        $userActiveShift = $this->shiftService->getActiveShiftByUserId(\Illuminate\Support\Facades\Auth::id());

        if (!$userActiveShift || $userActiveShift->type !== \App\Enums\PositionTypesEnum::VAMPIRE->value) {
            return view('vampire.flights.no_active_shift');
        }

        $activeShiftType = session('vampire_active_shift_type', ShiftTypeEnum::NIGHT->value);

        $flights = VampireFlight::with(['drone', 'flightPlan'])
            ->where('combat_shift_id', $userActiveShift->id)
            ->orderBy('start_time', 'desc')
            ->get();

        $drones = collect($userActiveShift->vampire_drones)
            ->where('status', 'active')
            ->filter(function ($drone) use ($activeShiftType) {
                return ($drone['shift_type'] ?? 'day') === 'both' || ($drone['shift_type'] ?? 'day') === $activeShiftType;
            });

        $plans = collect($userActiveShift->vampire_flight_plans)->where('status', 'planned');

        return view('vampire.flights.index', compact('userActiveShift', 'flights', 'drones', 'plans', 'activeShiftType'));
    }

    public function storePlan(Request $request)
    {
        $request->validate([
            'combat_shift_id' => 'required|exists:combat_shifts,id',
            'position_name' => 'required|string|max:255',
            'coordinates' => 'nullable|string|max:255',
        ]);

        $lastOrder = VampireFlightPlan::where('combat_shift_id', $request->combat_shift_id)
            ->max('order') ?? 0;

        VampireFlightPlan::create([
            'combat_shift_id' => $request->combat_shift_id,
            'position_name' => $request->position_name,
            'coordinates' => $request->coordinates,
            'order' => $lastOrder + 1,
            'status' => 'planned',
        ]);

        return redirect()->back()->with('success', 'Ціль додана до плану');
    }

    public function deletePlan(int $id)
    {
        $plan = VampireFlightPlan::findOrFail($id);
        $plan->delete();
        return redirect()->back()->with('success', 'Ціль видалена з плану');
    }

    public function editPlan(int $id)
    {
        $plan = VampireFlightPlan::findOrFail($id);
        $userActiveShift = $this->shiftService->getActiveShiftByUserId(\Illuminate\Support\Facades\Auth::id());

        if (!$userActiveShift || $plan->combat_shift_id !== $userActiveShift->id) {
            return redirect()->route('vampire.flights.index')
                ->with('error', 'Ви можете редагувати цілі лише своєї активної зміни');
        }

        return view('vampire.flight_plans.edit', compact('plan', 'userActiveShift'));
    }

    public function updatePlan(Request $request, int $id)
    {
        $plan = VampireFlightPlan::findOrFail($id);
        $request->validate([
            'position_name' => 'required|string|max:255',
            'coordinates' => 'nullable|string|max:255',
        ]);

        $plan->update($request->only(['position_name', 'coordinates']));

        return redirect()->route('vampire.flights.index')->with('success', 'Ціль оновлена');
    }

    public function store(Request $request)
    {
        $request->validate([
            'combat_shift_id' => 'required|exists:combat_shifts,id',
            'vampire_flight_plan_id' => 'nullable|exists:vampire_flight_plans,id',
            'vampire_drone_id' => 'required|exists:vampire_drones,id',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'stream_status' => 'boolean',
            'mission_type' => 'required|string',
            'result' => 'required|string',
            'comment' => 'nullable|string',
            'ammunition' => 'nullable|array',
            'ammunition.*.id' => 'nullable|exists:ammunition,id',
            'ammunition.*.quantity' => 'nullable|integer|min:1',
        ]);

        $data = $request->all();
        $data['shift_type'] = session('vampire_active_shift_type', ShiftTypeEnum::NIGHT->value);

        // Не зберігаємо БК, якщо тип місії не 'combat'
        if ($request->mission_type !== 'combat') {
            unset($data['ammunition']);
        }

        try {
            $flight = \Illuminate\Support\Facades\DB::transaction(function () use ($data, $request) {
                $flight = VampireFlight::create($data);

                if ($request->result === 'loss') {
                    $drone = \App\Models\VampireDrone::find($request->vampire_drone_id);
                    if ($drone) {
                        $drone->update([
                            'status' => 'lost',
                        ]);
                    }
                }

                if ($request->vampire_flight_plan_id) {
                    $plan = \App\Models\VampireFlightPlan::find($request->vampire_flight_plan_id);
                    if ($plan) {
                        // План вважається виконаним, якщо виліт був успішним (worked)
                        $plan->update(['status' => $request->result === 'worked' ? 'completed' : 'planned']);
                    }
                }

                // Списання БК
                if (!empty($data['ammunition'])) {
                    foreach ($data['ammunition'] as $ammunitionItem) {
                        if (empty($ammunitionItem['id'])) continue;

                        $flight->ammunition()->attach($ammunitionItem['id'], [
                            'quantity' => $ammunitionItem['quantity'] ?? 1
                        ]);

                        $ammunitionPivot = \Illuminate\Support\Facades\DB::table('combat_shift_ammunition')
                            ->where('combat_shift_id', $request->combat_shift_id)
                            ->where('ammunition_id', $ammunitionItem['id'])
                            ->where('quantity', '>', 0)
                            ->first();

                        if ($ammunitionPivot) {
                            \Illuminate\Support\Facades\DB::table('combat_shift_ammunition')
                                ->where('id', $ammunitionPivot->id)
                                ->decrement('quantity', $ammunitionItem['quantity'] ?? 1);
                        }
                    }
                }

                return $flight;
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Помилка при збереженні вильоту: ' . $e->getMessage())->withInput();
        }

        return redirect()->back()->with('success', 'Виліт зафіксовано');
    }

    public function destroy(int $id)
    {
        $flight = VampireFlight::with('ammunition')->findOrFail($id);

        \Illuminate\Support\Facades\DB::transaction(function () use ($flight) {
            if ($flight->result === 'loss') {
                $drone = \App\Models\VampireDrone::find($flight->vampire_drone_id);
                if ($drone) {
                    $drone->update([
                        'status' => 'active',
                    ]);
                }
            }

            // Повернення БК до бойової зміни
            foreach ($flight->ammunition as $ammo) {
                \Illuminate\Support\Facades\DB::table('combat_shift_ammunition')
                    ->where('combat_shift_id', $flight->combat_shift_id)
                    ->where('ammunition_id', $ammo->id)
                    ->increment('quantity', $ammo->pivot->quantity);
            }

            // Якщо виліт був прив'язаний до плану, повертаємо плану статус 'planned'
            if ($flight->vampire_flight_plan_id) {
                $plan = \App\Models\VampireFlightPlan::find($flight->vampire_flight_plan_id);
                if ($plan) {
                    $plan->update(['status' => 'planned']);
                }
            }

            $flight->delete();
        });

        return redirect()->back()->with('success', 'Виліт видалено');
    }

    public function edit(int $id)
    {
        $flight = VampireFlight::with('ammunition')->findOrFail($id);
        $userActiveShift = $this->shiftService->getActiveShiftByUserId(\Illuminate\Support\Facades\Auth::id());

        if (!$userActiveShift || $flight->combat_shift_id !== $userActiveShift->id) {
            return redirect()->route('vampire.flights.index')
                ->with('error', 'Ви можете редагувати вильоти лише своєї активної зміни');
        }

        $drones = collect($userActiveShift->vampire_drones)->where('status', 'active');
        // Додаємо дрон цього польоту до списку, якщо він не активний (наприклад, lost)
        $currentDrone = \App\Models\VampireDrone::find($flight->vampire_drone_id);
        if ($currentDrone && $currentDrone->status !== 'active') {
             $drones->push([
                 'id' => $currentDrone->id,
                 'name' => $currentDrone->name,
                 'serial_number' => $currentDrone->serial_number,
                 'status' => $currentDrone->status
             ]);
        }

        $plans = collect($userActiveShift->vampire_flight_plans)->where('status', 'planned');
        // Додаємо план цього польоту до списку
        if ($flight->vampire_flight_plan_id) {
            $currentPlan = \App\Models\VampireFlightPlan::find($flight->vampire_flight_plan_id);
            if ($currentPlan) {
                $plans->push([
                    'id' => $currentPlan->id,
                    'position_name' => $currentPlan->position_name,
                    'coordinates' => $currentPlan->coordinates,
                    'status' => $currentPlan->status
                ]);
            }
        }

        return view('vampire.flights.edit', compact('flight', 'userActiveShift', 'drones', 'plans'));
    }

    public function update(Request $request, int $id)
    {
        $flight = VampireFlight::with('ammunition')->findOrFail($id);
        $request->validate([
            'vampire_flight_plan_id' => 'nullable|exists:vampire_flight_plans,id',
            'vampire_drone_id' => 'required|exists:vampire_drones,id',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'stream_status' => 'boolean',
            'mission_type' => 'required|string',
            'result' => 'required|string',
            'comment' => 'nullable|string',
            'ammunition' => 'nullable|array',
            'ammunition.*.id' => 'nullable|exists:ammunition,id',
            'ammunition.*.quantity' => 'nullable|integer|min:1',
        ]);

        $data = $request->all();
        $data['stream_status'] = $request->boolean('stream_status');

        if ($request->mission_type !== 'combat') {
            unset($data['ammunition']);
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($data, $request, $flight) {
                // 1. Обробка зміни статусу дрона
                if ($flight->result !== $request->result || $flight->vampire_drone_id != $request->vampire_drone_id) {
                    // Повертаємо старий дрон до активного стану, якщо він був втрачений
                    if ($flight->result === 'loss') {
                        $oldDrone = \App\Models\VampireDrone::find($flight->vampire_drone_id);
                        if ($oldDrone) {
                            $oldDrone->update(['status' => 'active']);
                        }
                    }
                    // Списуємо новий дрон, якщо новий результат - втрата
                    if ($request->result === 'loss') {
                        $newDrone = \App\Models\VampireDrone::find($request->vampire_drone_id);
                        if ($newDrone) {
                            $newDrone->update(['status' => 'lost']);
                        }
                    }
                }

                // 2. Обробка зміни плану
                if ($flight->vampire_flight_plan_id != $request->vampire_flight_plan_id) {
                    // Повертаємо старий план у статус planned
                    if ($flight->vampire_flight_plan_id) {
                        $oldPlan = \App\Models\VampireFlightPlan::find($flight->vampire_flight_plan_id);
                        if ($oldPlan) {
                            $oldPlan->update(['status' => 'planned']);
                        }
                    }
                }

                // Оновлюємо статус плану (поточного або нового)
                if ($request->vampire_flight_plan_id) {
                    $plan = \App\Models\VampireFlightPlan::find($request->vampire_flight_plan_id);
                    if ($plan) {
                        $plan->update(['status' => $request->result === 'worked' ? 'completed' : 'planned']);
                    }
                }

                // 3. Повернення старого БК
                foreach ($flight->ammunition as $ammo) {
                    \Illuminate\Support\Facades\DB::table('combat_shift_ammunition')
                        ->where('combat_shift_id', $flight->combat_shift_id)
                        ->where('ammunition_id', $ammo->id)
                        ->increment('quantity', $ammo->pivot->quantity);
                }
                $flight->ammunition()->detach();

                // 4. Оновлення даних польоту
                $flight->update($data);

                // 5. Списання нового БК
                if (!empty($data['ammunition'])) {
                    foreach ($data['ammunition'] as $ammunitionItem) {
                        if (empty($ammunitionItem['id'])) continue;

                        $flight->ammunition()->attach($ammunitionItem['id'], [
                            'quantity' => $ammunitionItem['quantity'] ?? 1
                        ]);

                        $ammunitionPivot = \Illuminate\Support\Facades\DB::table('combat_shift_ammunition')
                            ->where('combat_shift_id', $flight->combat_shift_id)
                            ->where('ammunition_id', $ammunitionItem['id'])
                            ->first();

                        if ($ammunitionPivot) {
                            \Illuminate\Support\Facades\DB::table('combat_shift_ammunition')
                                ->where('id', $ammunitionPivot->id)
                                ->decrement('quantity', $ammunitionItem['quantity'] ?? 1);
                        }
                    }
                }
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Помилка при оновленні вильоту: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('vampire.flights.index')->with('success', 'Виліт оновлено');
    }
}
