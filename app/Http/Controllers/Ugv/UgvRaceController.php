<?php

namespace App\Http\Controllers\Ugv;

use App\Http\Controllers\Controller;
use App\Enums\ShiftTypeEnum;
use App\Models\UgvRace;
use App\Models\UgvRacePlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class UgvRaceController extends Controller
{
    public function __construct(private readonly \App\Services\CombatShiftsAdminService $shiftService)
    {
        $this->middleware(function ($request, $next) {
            if (Gate::denies('manage-ugv')) {
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

        session(['ugv_active_shift_type' => $request->shift_type]);

        return response()->json(['success' => true]);
    }

    public function index()
    {
        $userActiveShift = $this->shiftService->getActiveShiftByUserId(\Illuminate\Support\Facades\Auth::id());

        if (!$userActiveShift || $userActiveShift->type !== \App\Enums\PositionTypesEnum::UGV->value) {
            return view('ugv.races.no_active_shift');
        }

        $activeShiftType = session('ugv_active_shift_type', ShiftTypeEnum::DAY->value);

        $races = UgvRace::with(['drone', 'racePlan'])
            ->where('combat_shift_id', $userActiveShift->id)
            ->orderBy('start_time', 'desc')
            ->get()
            ->groupBy(function ($f) {
                return $f->start_time ? $f->start_time->format('Y-m-d') : 'unknown';
            });

        $drones = collect($userActiveShift->ugv_drones)
            ->where('status', 'active')
            ->filter(function ($drone) use ($activeShiftType) {
                return ($drone['shift_type'] ?? 'day') === 'both' || ($drone['shift_type'] ?? 'day') === $activeShiftType;
            });

        $plans = collect($userActiveShift->ugv_race_plans)->where('status', 'planned');

        return view('ugv.races.index', compact('userActiveShift', 'races', 'drones', 'plans', 'activeShiftType'));
    }

    public function storePlan(Request $request)
    {
        $request->validate([
            'combat_shift_id' => 'required|exists:combat_shifts,id',
            'position_name' => 'required|string|max:255',
        ]);

        $lastOrder = UgvRacePlan::where('combat_shift_id', $request->combat_shift_id)
            ->max('order') ?? 0;

        UgvRacePlan::create([
            'combat_shift_id' => $request->combat_shift_id,
            'position_name' => $request->position_name,
            'order' => $lastOrder + 1,
            'status' => 'planned',
        ]);

        return redirect()->back()->with('success', 'Ціль додана до плану');
    }

    public function deletePlan(int $id)
    {
        $plan = UgvRacePlan::findOrFail($id);
        $plan->delete();
        return redirect()->back()->with('success', 'Ціль видалена з плану');
    }

    public function editPlan(int $id)
    {
        $plan = UgvRacePlan::findOrFail($id);
        $userActiveShift = $this->shiftService->getActiveShiftByUserId(\Illuminate\Support\Facades\Auth::id());

        if (!$userActiveShift || $plan->combat_shift_id !== $userActiveShift->id) {
            return redirect()->route('ugv.races.index')
                ->with('error', 'Ви можете редагувати цілі лише своєї активної зміни');
        }

        return view('ugv.race_plans.edit', compact('plan', 'userActiveShift'));
    }

    public function updatePlan(Request $request, int $id)
    {
        $plan = UgvRacePlan::findOrFail($id);
        $request->validate([
            'position_name' => 'required|string|max:255',
        ]);

        $plan->update($request->only(['position_name']));

        return redirect()->route('ugv.races.index')->with('success', 'Ціль оновлена');
    }

    public function store(Request $request)
    {
        $request->validate([
            'combat_shift_id' => 'required|exists:combat_shifts,id',
            'ugv_race_plan_id' => 'nullable|exists:ugv_race_plans,id',
            'coordinates' => 'nullable|string|max:255',
            'ugv_drone_id' => 'required|exists:ugv_drones,id',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'stream_status' => 'boolean',
            'mission_type' => 'required|string',
            'result' => 'required|string|in:worked,loss,not_worked',
            'comment' => 'nullable|string',
            'video' => 'nullable|file|mimes:mp4,mov,avi,wmv|max:76800',
            'ammunition' => 'nullable|array',
            'ammunition.*.id' => 'nullable|exists:ammunition,id',
            'ammunition.*.quantity' => 'nullable|integer|min:1',
        ]);

        if ($request->hasFile('video')) {
            $data = $request->all();
            $data['video_path'] = $request->file('video')->store('ugv/races/videos', 'public');
        } else {
            $data = $request->all();
        }

        $data['shift_type'] = session('ugv_active_shift_type', ShiftTypeEnum::DAY->value);
        $data['stream_status'] = $request->boolean('stream_status');

        // Не зберігаємо БК, якщо тип місії не 'combat'
        if ($request->mission_type !== 'combat') {
            unset($data['ammunition']);
        }

        try {
            $race = \Illuminate\Support\Facades\DB::transaction(function () use ($data, $request) {
                $race = UgvRace::create($data);
                if ($request->result === 'loss') {
                    $drone = \App\Models\UgvDrone::find($request->ugv_drone_id);
                    if ($drone) {
                        $drone->update([
                            'status' => 'lost',
                            'lost_at' => now(),
                        ]);
                    }
                }

                if ($request->ugv_race_plan_id) {
                    $plan = \App\Models\UgvRacePlan::find($request->ugv_race_plan_id);
                    if ($plan) {
                        // План вважається виконаним при будь-якому результаті рейсу (прибирається з плану)
                        $plan->update(['status' => 'completed']);
                    }
                }

                // Списання БК
                if (!empty($data['ammunition'])) {
                    foreach ($data['ammunition'] as $ammunitionItem) {
                        if (empty($ammunitionItem['id'])) continue;

                        $race->ammunition()->attach($ammunitionItem['id'], [
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

                return $race;
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Помилка при збереженні рейсу: ' . $e->getMessage())->withInput();
        }

        return redirect()->back()->with('success', 'Рейс зафіксовано');
    }

    public function destroy(int $id)
    {
        $race = UgvRace::with('ammunition')->findOrFail($id);

        \Illuminate\Support\Facades\DB::transaction(function () use ($race) {
            if ($race->result === 'loss') {
                $drone = \App\Models\UgvDrone::find($race->ugv_drone_id);
                if ($drone) {
                    $drone->update([
                        'status' => 'active',
                        'lost_at' => null,
                    ]);
                }
            }

            // Повернення БК до бойової зміни
            foreach ($race->ammunition as $ammo) {
                \Illuminate\Support\Facades\DB::table('combat_shift_ammunition')
                    ->where('combat_shift_id', $race->combat_shift_id)
                    ->where('ammunition_id', $ammo->id)
                    ->increment('quantity', $ammo->pivot->quantity);
            }

            // Якщо рейс був прив'язаний до плану, повертаємо плану статус 'planned'
            if ($race->ugv_race_plan_id) {
                $plan = \App\Models\UgvRacePlan::find($race->ugv_race_plan_id);
                if ($plan) {
                    $plan->update(['status' => 'planned']);
                }
            }

            if ($race->video_path) {
                Storage::disk('public')->delete($race->video_path);
            }

            $race->delete();
        });

        return redirect()->back()->with('success', 'Рейс видалено');
    }

    public function edit(int $id)
    {
        $race = UgvRace::with('ammunition')->findOrFail($id);
        $userActiveShift = $this->shiftService->getActiveShiftByUserId(\Illuminate\Support\Facades\Auth::id());

        if (!$userActiveShift || $race->combat_shift_id !== $userActiveShift->id) {
            return redirect()->route('ugv.races.index')
                ->with('error', 'Ви можете редагувати рейси лише своєї активної зміни');
        }

        $drones = collect($userActiveShift->ugv_drones)->where('status', 'active');
        // Додаємо дрон цього рейсу до списку, якщо він не активний (наприклад, lost)
        $currentDrone = \App\Models\UgvDrone::find($race->ugv_drone_id);
        if ($currentDrone && $currentDrone->status !== 'active') {
             $drones->push([
                 'id' => $currentDrone->id,
                 'name' => $currentDrone->name,
                 'serial_number' => $currentDrone->serial_number,
                 'status' => $currentDrone->status,
                 'status_color' => $currentDrone->status_color
             ]);
        }

        $plans = collect($userActiveShift->ugv_race_plans)->where('status', 'planned');
        // Додаємо план цього рейсу до списку
        if ($race->ugv_race_plan_id) {
            $currentPlan = \App\Models\UgvRacePlan::find($race->ugv_race_plan_id);
            if ($currentPlan) {
                $plans->push([
                    'id' => $currentPlan->id,
                    'position_name' => $currentPlan->position_name,
                    'coordinates' => $currentPlan->coordinates,
                    'status' => $currentPlan->status
                ]);
            }
        }

        return view('ugv.races.edit', compact('race', 'userActiveShift', 'drones', 'plans'));
    }

    public function update(Request $request, int $id)
    {
        $race = UgvRace::with('ammunition')->findOrFail($id);
        $request->validate([
            'ugv_race_plan_id' => 'nullable|exists:ugv_race_plans,id',
            'coordinates' => 'nullable|string|max:255',
            'ugv_drone_id' => 'required|exists:ugv_drones,id',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'stream_status' => 'boolean',
            'mission_type' => 'required|string',
            'result' => 'required|string|in:worked,loss,not_worked',
            'comment' => 'nullable|string',
            'video' => 'nullable|file|mimes:mp4,mov,avi,wmv|max:76800',
            'ammunition' => 'nullable|array',
            'ammunition.*.id' => 'nullable|exists:ammunition,id',
            'ammunition.*.quantity' => 'nullable|integer|min:1',
        ]);

        $data = $request->all();
        $data['stream_status'] = $request->boolean('stream_status');

        if ($request->hasFile('video')) {
            if ($race->video_path) {
                Storage::disk('public')->delete($race->video_path);
            }
            $data['video_path'] = $request->file('video')->store('ugv/races/videos', 'public');
        }

        if ($request->mission_type !== 'combat') {
            unset($data['ammunition']);
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($data, $request, $race) {
                // 1. Обробка зміни статусу дрона
                if ($race->result !== $request->result || $race->ugv_drone_id != $request->ugv_drone_id) {
                    // Повертаємо старий дрон до активного стану, якщо він був втрачений
                    if ($race->result === 'loss') {
                        $oldDrone = \App\Models\UgvDrone::find($race->ugv_drone_id);
                        if ($oldDrone) {
                            $oldDrone->update([
                                'status' => 'active',
                                'lost_at' => null,
                            ]);
                        }
                    }
                    // Списуємо новий дрон, якщо новий результат - втрата
                    if ($request->result === 'loss') {
                        $newDrone = \App\Models\UgvDrone::find($request->ugv_drone_id);
                        if ($newDrone) {
                            $newDrone->update([
                                'status' => 'lost',
                                'lost_at' => now(),
                            ]);
                        }
                    }
                }

                // 2. Обробка зміни плану
                if ($race->ugv_race_plan_id != $request->ugv_race_plan_id) {
                    // Повертаємо старий план у статус planned
                    if ($race->ugv_race_plan_id) {
                        $oldPlan = \App\Models\UgvRacePlan::find($race->ugv_race_plan_id);
                        if ($oldPlan) {
                            $oldPlan->update(['status' => 'planned']);
                        }
                    }
                }

                // Оновлюємо статус плану (поточного або нового)
                if ($request->ugv_race_plan_id) {
                    $plan = \App\Models\UgvRacePlan::find($request->ugv_race_plan_id);
                    if ($plan) {
                        // План вважається виконаним при будь-якому результаті рейсу (прибирається з плану)
                        $plan->update(['status' => 'completed']);
                    }
                }

                // 3. Повернення старого БК
                foreach ($race->ammunition as $ammo) {
                    \Illuminate\Support\Facades\DB::table('combat_shift_ammunition')
                        ->where('combat_shift_id', $race->combat_shift_id)
                        ->where('ammunition_id', $ammo->id)
                        ->increment('quantity', $ammo->pivot->quantity);
                }
                $race->ammunition()->detach();

                // 4. Оновлення даних рейсу
                $race->update($data);

                // 5. Списання нового БК
                if (!empty($data['ammunition'])) {
                    foreach ($data['ammunition'] as $ammunitionItem) {
                        if (empty($ammunitionItem['id'])) continue;

                        $race->ammunition()->attach($ammunitionItem['id'], [
                            'quantity' => $ammunitionItem['quantity'] ?? 1
                        ]);

                        $ammunitionPivot = \Illuminate\Support\Facades\DB::table('combat_shift_ammunition')
                            ->where('combat_shift_id', $race->combat_shift_id)
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
            return redirect()->back()->with('error', 'Помилка при оновленні рейсу: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('ugv.races.index')->with('success', 'Рейс оновлено');
    }

    public function downloadVideo(int $id)
    {
        $race = UgvRace::findOrFail($id);

        if (!$race->video_path || !Storage::disk('public')->exists($race->video_path)) {
            return redirect()->back()->with('error', 'Відео не знайдено');
        }

        return Storage::disk('public')->download($race->video_path);
    }
}
