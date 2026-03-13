<?php

namespace App\Http\Controllers\Recon;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReconFlightStoreRequest;
use App\Http\Requests\ReconFlightUpdateRequest;
use App\Models\ReconFlight;
use App\Models\ReconDrone;
use App\Models\Ammunition;
use App\Models\CombatShift;
use App\Enums\ReconMissionResultsEnum;
use App\Enums\PositionTypesEnum;
use App\Enums\ShiftTypeEnum;
use App\Services\CombatShiftsAdminService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReconFlightController extends Controller
{
    public function __construct(
        private readonly CombatShiftsAdminService $shiftService
    ) {}

    public function downloadVideo(int $id): StreamedResponse|RedirectResponse
    {
        $flight = ReconFlight::findOrFail($id);

        if (!$flight->video_path || !Storage::disk('public')->exists($flight->video_path)) {
            return redirect()->back()->with('error', 'Відео не знайдено');
        }

        return Storage::disk('public')->download($flight->video_path);
    }

    public function setShiftType(Request $request): JsonResponse
    {
        $request->validate([
            'shift_type' => 'required|string|in:day,night'
        ]);

        session(['recon_active_shift_type' => $request->shift_type]);

        return response()->json(['success' => true]);
    }

    public function index()
    {
        $userActiveShift = $this->shiftService->getActiveShiftByUserId(Auth::id());

        if (!$userActiveShift || $userActiveShift->type !== PositionTypesEnum::RECON->value) {
            return view('recon.flights.no_active_shift');
        }

        $activeShiftType = session('recon_active_shift_type', ShiftTypeEnum::DAY->value);

        $flights = ReconFlight::with(['drone', 'ammunition'])
            ->whereIn('recon_drone_id', collect($userActiveShift->recon_drones)->pluck('id'))
            ->orderBy('flight_time', 'desc')
            ->get();

        $drones = collect($userActiveShift->recon_drones)
            ->where('status', 'active')
            ->filter(function ($drone) use ($activeShiftType) {
                return ($drone['shift_type'] ?? 'day') === 'both' || ($drone['shift_type'] ?? 'day') === $activeShiftType;
            });
        $ammunition = collect($userActiveShift->ammunition)->where('quantity', '>', 0);

        return view('recon.flights.index', compact('userActiveShift', 'flights', 'drones', 'ammunition', 'activeShiftType'));
    }

    public function store(ReconFlightStoreRequest $request): RedirectResponse
    {
        $userActiveShift = $this->shiftService->getActiveShiftByUserId(Auth::id());

        if (!$userActiveShift || $userActiveShift->type !== PositionTypesEnum::RECON->value) {
            return redirect()->back()->with('error', 'У вас немає активного чергування RECON');
        }

        $data = $request->validated();

        // Не зберігаємо БК, якщо тип місії не 'combat'
        if (($data['mission_type'] ?? null) !== \App\Enums\ReconMissionTypesEnum::COMBAT->value) {
            unset($data['ammunition']);
        }

        $data['stream_status'] = $request->boolean('stream_status');
        $data['shift_type'] = session('recon_active_shift_type', ShiftTypeEnum::DAY->value);
        $data['combat_shift_id'] = $userActiveShift->id;

        if ($request->hasFile('video')) {
            $data['video_path'] = $request->file('video')->store('recon/flights/videos', 'public');
        }

        // Логування перед збереженням
        \Log::info('Спроба збереження польоту RECON', [
            'userId' => Auth::id(),
            'shiftId' => $userActiveShift->id,
            'data' => collect($data)->except(['video'])->toArray()
        ]);

        try {
            DB::transaction(function () use ($data, $userActiveShift) {
                $flight = ReconFlight::create($data);

                // 1. Списання дрона лише при втраті
                if ($flight->result === ReconMissionResultsEnum::BOARD_LOOSED) {
                    ReconDrone::where('id', $flight->recon_drone_id)->update(['status' => 'lost']);
                }

                // 2. Списання БК (завжди, якщо вибрано)
                if (!empty($data['ammunition'])) {
                    foreach ($data['ammunition'] as $ammunitionItem) {
                        if (empty($ammunitionItem['id'])) continue;

                        $flight->ammunition()->attach($ammunitionItem['id'], [
                            'quantity' => $ammunitionItem['quantity'] ?? 1
                        ]);

                        $ammunitionPivot = DB::table('combat_shift_ammunition')
                            ->where('combat_shift_id', $userActiveShift->id)
                            ->where('ammunition_id', $ammunitionItem['id'])
                            ->where('quantity', '>', 0)
                            ->first();

                        if ($ammunitionPivot) {
                            DB::table('combat_shift_ammunition')
                                ->where('id', $ammunitionPivot->id)
                                ->decrement('quantity', $ammunitionItem['quantity'] ?? 1);
                        }
                    }
                }
            });
        } catch (\Exception $e) {
            \Log::error('Помилка при збереженні польоту RECON: ' . $e->getMessage(), [
                'exception' => $e,
                'data' => $data,
                'userId' => Auth::id()
            ]);
            return redirect()->back()->with('error', 'Помилка при збереженні польоту: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('recon.flights.index')
            ->with('success', 'Виліт RECON успішно додано');
    }

    public function edit(int $id)
    {
        $flight = ReconFlight::with('ammunition')->findOrFail($id);
        $userActiveShift = $this->shiftService->getActiveShiftByUserId(Auth::id());

        if (!$userActiveShift || !collect($userActiveShift->recon_drones)->pluck('id')->contains($flight->recon_drone_id)) {
            return redirect()->route('recon.flights.index')
                ->with('error', 'Ви можете редагувати вильоти лише своєї активної зміни');
        }

        $activeShiftType = $flight->shift_type->value;
        $drones = collect($userActiveShift->recon_drones)
            ->filter(function ($drone) use ($activeShiftType) {
                return ($drone['shift_type'] ?? 'day') === 'both' || ($drone['shift_type'] ?? 'day') === $activeShiftType;
            });
        $ammunition = collect($userActiveShift->ammunition)->where('quantity', '>', 0);

        return view('recon.flights.edit', compact('flight', 'userActiveShift', 'drones', 'ammunition', 'activeShiftType'));
    }

    public function update(ReconFlightUpdateRequest $request, int $id): RedirectResponse
    {
        $flight = ReconFlight::with('ammunition')->findOrFail($id);
        $userActiveShift = $this->shiftService->getActiveShiftByUserId(Auth::id());

        if (!$userActiveShift || !collect($userActiveShift->recon_drones)->pluck('id')->contains($flight->recon_drone_id)) {
            return redirect()->route('recon.flights.index')
                ->with('error', 'Ви можете редагувати вильоти лише своєї активної зміни');
        }

        $data = $request->validated();
        $data['stream_status'] = $request->boolean('stream_status');

        if ($request->hasFile('video')) {
            if ($flight->video_path) {
                Storage::disk('public')->delete($flight->video_path);
            }
            $data['video_path'] = $request->file('video')->store('recon/flights/videos', 'public');
        }

        DB::transaction(function () use ($data, $flight, $userActiveShift) {
            // 1. Обробка зміни статусу дрона (якщо результат змінився)
            if ($flight->result !== $data['result']) {
                // Якщо раніше була втрата, а тепер ні - активуємо дрон
                if ($flight->result === ReconMissionResultsEnum::BOARD_LOOSED && $data['result'] !== ReconMissionResultsEnum::BOARD_LOOSED) {
                    ReconDrone::where('id', $flight->recon_drone_id)->update(['status' => 'active']);
                }
                // Якщо раніше не була втрата, а тепер втрата - списуємо дрон
                elseif ($flight->result !== ReconMissionResultsEnum::BOARD_LOOSED && $data['result'] === ReconMissionResultsEnum::BOARD_LOOSED) {
                    ReconDrone::where('id', $data['recon_drone_id'])->update(['status' => 'lost']);
                }
            }

            // 2. Повернення старого БК до бойової зміни
            foreach ($flight->ammunition as $ammo) {
                DB::table('combat_shift_ammunition')
                    ->where('combat_shift_id', $userActiveShift->id)
                    ->where('ammunition_id', $ammo->id)
                    ->increment('quantity', $ammo->pivot->quantity);
            }
            $flight->ammunition()->detach();

            // 3. Оновлення польоту
            $flight->update($data);

            // 4. Списання нового БК
            if (!empty($data['ammunition'])) {
                foreach ($data['ammunition'] as $ammunitionItem) {
                    if (empty($ammunitionItem['id'])) continue;

                    $flight->ammunition()->attach($ammunitionItem['id'], [
                        'quantity' => $ammunitionItem['quantity'] ?? 1
                    ]);

                    $ammunitionPivot = DB::table('combat_shift_ammunition')
                        ->where('combat_shift_id', $userActiveShift->id)
                        ->where('ammunition_id', $ammunitionItem['id'])
                        ->where('quantity', '>', 0)
                        ->first();

                    if ($ammunitionPivot) {
                        DB::table('combat_shift_ammunition')
                            ->where('id', $ammunitionPivot->id)
                            ->decrement('quantity', $ammunitionItem['quantity'] ?? 1);
                    }
                }
            }
        });

        return redirect()->route('recon.flights.index')
            ->with('success', 'Виліт RECON успішно оновлено');
    }

    public function destroy(int $id): RedirectResponse
    {
        $flight = ReconFlight::with('ammunition')->findOrFail($id);
        $userActiveShift = $this->shiftService->getActiveShiftByUserId(Auth::id());

        // Дозволяємо видаляти лише свої польоти (з поточної зміни)
        if (!$userActiveShift || !collect($userActiveShift->recon_drones)->pluck('id')->contains($flight->recon_drone_id)) {
             return redirect()->route('recon.flights.index')
                ->with('error', 'Ви можете видаляти вильоти лише своєї активної зміни');
        }

        DB::transaction(function () use ($flight, $userActiveShift) {
            // 1. Повернення статусу дрона, якщо він був втрачений
            if ($flight->result === ReconMissionResultsEnum::BOARD_LOOSED) {
                ReconDrone::where('id', $flight->recon_drone_id)->update(['status' => 'active']);
            }

            // 2. Повернення БК до бойової зміни
            foreach ($flight->ammunition as $ammo) {
                DB::table('combat_shift_ammunition')
                    ->where('combat_shift_id', $userActiveShift->id)
                    ->where('ammunition_id', $ammo->id)
                    ->increment('quantity', $ammo->pivot->quantity);
            }

            // 3. Видалення відео
            if ($flight->video_path && $flight->isForceDeleting()) {
                Storage::disk('public')->delete($flight->video_path);
            }

            // 4. Видалення запису про політ
            $flight->delete();
        });

        return redirect()->route('recon.flights.index')
            ->with('success', 'Виліт успішно видалено, майно повернено');
    }
}
