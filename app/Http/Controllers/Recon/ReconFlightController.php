<?php

namespace App\Http\Controllers\Recon;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReconFlightStoreRequest;
use App\Models\ReconFlight;
use App\Models\ReconDrone;
use App\Models\Ammunition;
use App\Models\CombatShift;
use App\Enums\ReconMissionResultsEnum;
use App\Enums\PositionTypesEnum;
use App\Services\CombatShiftsAdminService;
use Illuminate\Http\RedirectResponse;
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

    public function index()
    {
        $userActiveShift = $this->shiftService->getActiveShiftByUserId(Auth::id());

        if (!$userActiveShift || $userActiveShift->type !== PositionTypesEnum::RECON->value) {
            return view('recon.flights.no_active_shift');
        }

        $flights = ReconFlight::with(['drone', 'ammunition'])
            ->whereIn('recon_drone_id', collect($userActiveShift->recon_drones)->pluck('id'))
            ->orderBy('flight_time', 'desc')
            ->get();

        $drones = collect($userActiveShift->recon_drones)->where('status', 'active');
        $ammunition = collect($userActiveShift->ammunition)->where('quantity', '>', 0);

        return view('recon.flights.index', compact('userActiveShift', 'flights', 'drones', 'ammunition'));
    }

    public function store(ReconFlightStoreRequest $request): RedirectResponse
    {
        $userActiveShift = $this->shiftService->getActiveShiftByUserId(Auth::id());

        if (!$userActiveShift || $userActiveShift->type !== PositionTypesEnum::RECON->value) {
            return redirect()->back()->with('error', 'У вас немає активного чергування RECON');
        }

        $data = $request->validated();

        if ($request->hasFile('video')) {
            $data['video_path'] = $request->file('video')->store('recon/flights/videos', 'public');
        }

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

        return redirect()->route('recon.flights.index')
            ->with('success', 'Виліт RECON успішно додано');
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
            if ($flight->video_path) {
                Storage::disk('public')->delete($flight->video_path);
            }

            // 4. Видалення запису про політ (зв'язки в recon_flight_ammunition видаляться каскадно або вручну)
            // Оскільки ми використовуємо detach() або просто видаляємо політ,
            // якщо в міграції було onDelete('cascade'), то зв'язки видаляться самі.
            $flight->ammunition()->detach();
            $flight->delete();
        });

        return redirect()->route('recon.flights.index')
            ->with('success', 'Виліт успішно видалено, майно повернено');
    }
}
