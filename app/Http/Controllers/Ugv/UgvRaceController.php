<?php

namespace App\Http\Controllers\Ugv;

use App\Http\Controllers\Controller;
use App\Models\UgvRace;
use App\Models\UgvRacePlan;
use App\Services\CombatShiftsAdminService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class UgvRaceController extends Controller
{
    public function __construct(private readonly CombatShiftsAdminService $shiftService)
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
            'race_id' => 'required|exists:ugv_races,id',
            'shift_type' => 'required|in:day,night',
        ]);

        $race = UgvRace::findOrFail($request->race_id);
        $race->update(['shift_type' => $request->shift_type]);

        return response()->json(['success' => true]);
    }

    public function index()
    {
        return redirect()->route('ugv.combat_shifts.index');
    }

    public function storePlan(Request $request)
    {
        $validated = $request->validate([
            'combat_shift_id' => 'required|exists:combat_shifts,id',
            'position_name' => 'required|string|max:255',
            'coordinates' => 'nullable|string|max:255',
            'order' => 'nullable|integer',
        ]);

        UgvRacePlan::create($validated);

        return redirect()->back()->with('success', 'Точку додано до плану');
    }

    public function deletePlan(int $id)
    {
        UgvRacePlan::destroy($id);
        return redirect()->back()->with('success', 'Точку видалено з плану');
    }

    public function editPlan(int $id)
    {
        $plan = UgvRacePlan::findOrFail($id);
        return response()->json($plan);
    }

    public function updatePlan(Request $request, int $id)
    {
        $plan = UgvRacePlan::findOrFail($id);
        $validated = $request->validate([
            'position_name' => 'required|string|max:255',
            'coordinates' => 'nullable|string|max:255',
            'status' => 'required|in:planned,completed,skipped',
        ]);
        $plan->update($validated);
        return redirect()->back()->with('success', 'Точку плану оновлено');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'combat_shift_id' => 'required|exists:combat_shifts,id',
            'ugv_race_plan_id' => 'nullable|exists:ugv_race_plans,id',
            'ugv_drone_id' => 'required|exists:ugv_drones,id',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'mission_type' => 'required|string',
            'result' => 'required|string',
            'stream_status' => 'nullable|boolean',
            'comment' => 'nullable|string',
            'coordinates' => 'nullable|string|max:255',
            'ammunition' => 'nullable|array',
            'ammunition.*.id' => 'required|exists:ammunition,id',
            'ammunition.*.quantity' => 'required|integer|min:1',
            'video' => 'nullable|file|mimes:mp4,mov,avi,mkv|max:102400',
        ]);

        $raceData = collect($validated)->except(['ammunition', 'video'])->toArray();
        $raceData['stream_status'] = $request->has('stream_status');

        if ($request->hasFile('video')) {
            $path = $request->file('video')->store('races/ugv', 'public');
            $raceData['video_path'] = $path;
        }

        $race = UgvRace::create($raceData);

        if (!empty($validated['ammunition'])) {
            foreach ($validated['ammunition'] as $ammo) {
                $race->ammunition()->attach($ammo['id'], ['quantity' => $ammo['quantity']]);
                $this->shiftService->updateAmmunitionQuantity($validated['combat_shift_id'], $ammo['id'], -$ammo['quantity']);
            }
        }

        if ($validated['result'] === 'loss') {
            $drone = \App\Models\UgvDrone::find($validated['ugv_drone_id']);
            $drone->update(['status' => 'lost', 'lost_at' => now()]);
        }

        if ($request->ugv_race_plan_id) {
            UgvRacePlan::find($request->ugv_race_plan_id)->update(['status' => 'completed']);
        }

        return redirect()->back()->with('success', 'Рейс додано');
    }

    public function destroy(int $id)
    {
        $race = UgvRace::findOrFail($id);
        $shiftId = $race->combat_shift_id;

        foreach ($race->ammunition as $ammo) {
            $this->shiftService->updateAmmunitionQuantity($shiftId, $ammo->id, $ammo->pivot->quantity);
        }

        if ($race->result === 'loss') {
            $race->drone->update(['status' => 'active', 'lost_at' => null]);
        }

        if ($race->video_path) {
            Storage::disk('public')->delete($race->video_path);
        }

        $race->delete();

        return redirect()->back()->with('success', 'Рейс видалено');
    }

    public function edit(int $id)
    {
        $race = UgvRace::with('ammunition')->findOrFail($id);
        return response()->json([
            'id' => $race->id,
            'ugv_drone_id' => $race->ugv_drone_id,
            'ugv_race_plan_id' => $race->ugv_race_plan_id,
            'start_time' => $race->start_time?->format('Y-m-d\TH:i'),
            'end_time' => $race->end_time?->format('Y-m-d\TH:i'),
            'mission_type' => $race->mission_type,
            'result' => $race->result,
            'stream_status' => $race->stream_status,
            'comment' => $race->comment,
            'coordinates' => $race->coordinates,
            'ammunition' => $race->ammunition->map(fn($a) => [
                'id' => $a->id,
                'quantity' => $a->pivot->quantity,
            ]),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $race = UgvRace::findOrFail($id);
        $validated = $request->validate([
            'ugv_drone_id' => 'required|exists:ugv_drones,id',
            'ugv_race_plan_id' => 'nullable|exists:ugv_race_plans,id',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'mission_type' => 'required|string',
            'result' => 'required|string',
            'stream_status' => 'nullable|boolean',
            'comment' => 'nullable|string',
            'coordinates' => 'nullable|string|max:255',
            'ammunition' => 'nullable|array',
            'ammunition.*.id' => 'required|exists:ammunition,id',
            'ammunition.*.quantity' => 'required|integer|min:1',
            'video' => 'nullable|file|mimes:mp4,mov,avi,mkv|max:102400',
        ]);

        foreach ($race->ammunition as $ammo) {
            $this->shiftService->updateAmmunitionQuantity($race->combat_shift_id, $ammo->id, $ammo->pivot->quantity);
        }

        if ($race->result === 'loss' && $validated['result'] !== 'loss') {
            $race->drone->update(['status' => 'active', 'lost_at' => null]);
        }

        $raceData = collect($validated)->except(['ammunition', 'video'])->toArray();
        $raceData['stream_status'] = $request->has('stream_status');

        if ($request->hasFile('video')) {
            if ($race->video_path) {
                Storage::disk('public')->delete($race->video_path);
            }
            $path = $request->file('video')->store('races/ugv', 'public');
            $raceData['video_path'] = $path;
        }

        $race->update($raceData);

        $race->ammunition()->detach();
        if (!empty($validated['ammunition'])) {
            foreach ($validated['ammunition'] as $ammo) {
                $race->ammunition()->attach($ammo['id'], ['quantity' => $ammo['quantity']]);
                $this->shiftService->updateAmmunitionQuantity($race->combat_shift_id, $ammo['id'], -$ammo['quantity']);
            }
        }

        if ($validated['result'] === 'loss') {
            $drone = \App\Models\UgvDrone::find($validated['ugv_drone_id']);
            $drone->update(['status' => 'lost', 'lost_at' => now()]);
        }

        return redirect()->back()->with('success', 'Рейс оновлено');
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
