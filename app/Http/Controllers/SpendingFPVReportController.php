<?php

namespace App\Http\Controllers;

use App\Presenters\FPVSpendingReportPresenter;
use App\Services\SpendingFPVReportService;
use Illuminate\Support\Facades\Auth;

class SpendingFPVReportController extends Controller
{
    public function __construct(
        private readonly SpendingFPVReportService $spendingFPVReportService,
    )
    {
    }

    public function spendFPVReport(int $shiftId)
    {
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
        if (!$reportData = $this->spendingFPVReportService->getReportByUserId(Auth::id())) {
            return redirect()->route('flight_operations.index')
                ->with('error', 'У вас немає активної зміни.');
        }

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
