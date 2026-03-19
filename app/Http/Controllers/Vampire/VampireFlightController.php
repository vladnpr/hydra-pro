<?php

namespace App\Http\Controllers\Vampire;

use App\Http\Controllers\Controller;
use App\Models\VampireFlight;
use App\Models\VampireFlightPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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

    public function index()
    {
        $userActiveShift = $this->shiftService->getActiveShiftByUserId(\Illuminate\Support\Facades\Auth::id());

        if (!$userActiveShift || $userActiveShift->type !== \App\Enums\PositionTypesEnum::VAMPIRE->value) {
            return view('vampire.flights.no_active_shift');
        }

        $flights = VampireFlight::with(['drone', 'flightPlan'])
            ->where('combat_shift_id', $userActiveShift->id)
            ->orderBy('flight_time', 'desc')
            ->get();

        $drones = collect($userActiveShift->vampire_drones)->where('status', 'active');
        $plans = collect($userActiveShift->vampire_flight_plans)->where('status', 'planned');

        return view('vampire.flights.index', compact('userActiveShift', 'flights', 'drones', 'plans'));
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

    public function store(Request $request)
    {
        $request->validate([
            'combat_shift_id' => 'required|exists:combat_shifts,id',
            'vampire_flight_plan_id' => 'nullable|exists:vampire_flight_plans,id',
            'vampire_drone_id' => 'required|exists:vampire_drones,id',
            'flight_time' => 'required|date',
            'stream_status' => 'boolean',
            'mission_type' => 'required|string',
            'result' => 'required|string',
            'comment' => 'nullable|string',
            'ammunition' => 'nullable|array',
            'ammunition.*.id' => 'nullable|exists:ammunition,id',
            'ammunition.*.quantity' => 'nullable|integer|min:1',
        ]);

        $data = $request->all();

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
}
