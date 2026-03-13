<?php

namespace App\Http\Controllers;

use App\Presenters\FPVSpendingReportPresenter;
use App\Services\SpendingFPVReportService;
use App\Services\SpendingReconReportService;
use Illuminate\Support\Facades\Auth;

class SpendingFPVReportController extends Controller
{
    public function __construct(
        private readonly SpendingFPVReportService $spendingFPVReportService,
        private readonly SpendingReconReportService $spendingReconReportService,
    )
    {
    }

    public function spendFPVReport(int $shiftId)
    {
        $shift = \App\Models\CombatShift::findOrFail($shiftId);

        if ($shift->type === \App\Enums\PositionTypesEnum::RECON->value) {
            $reportData = $this->spendingReconReportService->getSpendingReport($shiftId);
            return view('recon.combat_shifts.spending_report', $reportData);
        }

        $reportData = $this->spendingFPVReportService->getSpendingFPVReport($shiftId);

        $presenter = new FPVSpendingReportPresenter(
            $reportData->getShiftId(),
            $reportData->getPositionName(),
            $reportData->getDrones(),
            $reportData->getAmmunition(),
        );

        return view('admin.combat_shifts.spending_fpv_report', [
            'presenter' => $presenter
        ]);
    }

    public function activeSpendFPVReport()
    {
        $activeShift = \App\Models\CombatShift::whereHas('users', function($q) {
                $q->where('users.id', Auth::id());
            })
            ->where('status', 'opened')
            ->first();

        if (!$activeShift) {
            return redirect()->route('flight_operations.index')
                ->with('error', 'У вас немає активної зміни.');
        }

        if ($activeShift->type === \App\Enums\PositionTypesEnum::RECON->value) {
            $reportData = $this->spendingReconReportService->getSpendingReport($activeShift->id);
            return view('recon.combat_shifts.spending_report', $reportData);
        }

        $reportData = $this->spendingFPVReportService->getReportByUserId(Auth::id());

        $presenter = new FPVSpendingReportPresenter(
            $reportData->getShiftId(),
            $reportData->getPositionName(),
            $reportData->getDrones(),
            $reportData->getAmmunition(),
        );

        return view('admin.combat_shifts.spending_fpv_report', [
            'presenter' => $presenter
        ]);
    }
}
