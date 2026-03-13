<?php

namespace App\Services;

use App\Models\CombatShift;
use App\Models\ReconFlight;
use App\Enums\ReconMissionResultsEnum;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SpendingReconReportService
{
    /**
     * @param int $shiftId
     * @return array
     */
    public function getSpendingReport(int $shiftId): array
    {
        $shift = CombatShift::with('position')->findOrFail($shiftId);

        // 1. Отримуємо всі польоти за цю зміну
        $flights = ReconFlight::with(['drone', 'ammunition'])
            ->where('combat_shift_id', $shiftId)
            ->get();

        // 2. Витрачені дрони (лише ті, що були втрачені в цій зміні)
        // Примітка: Ми шукаємо польоти з результатом BOARD_LOOSED
        $lostDrones = $flights->filter(function ($flight) {
            return $flight->result === ReconMissionResultsEnum::BOARD_LOOSED;
        })->map(function ($flight) {
            return $flight->drone->name . ' ' . $flight->drone->serial_number;
        })->unique()->values();

        // 3. Витрачений БК (сума по всіх польотах)
        $ammunitionSpending = DB::table('recon_flight_ammunition as rfa')
            ->join('recon_flights as rf', 'rfa.recon_flight_id', '=', 'rf.id')
            ->join('ammunition as a', 'rfa.ammunition_id', '=', 'a.id')
            ->where('rf.combat_shift_id', $shiftId)
            ->select('a.name', DB::raw('SUM(rfa.quantity) as total_quantity'))
            ->groupBy('a.name')
            ->get();

        return [
            'shift_id' => $shift->id,
            'position_name' => $shift->position->name,
            'lost_drones' => $lostDrones,
            'ammunition' => $ammunitionSpending,
        ];
    }
}
