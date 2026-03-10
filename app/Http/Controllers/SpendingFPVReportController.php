<?php

namespace App\Http\Controllers;

use App\Presenters\FPVSpendingReportPresenter;
use App\Services\SpendingFPVReportService;

class SpendingFPVReportController extends Controller
{
    public function __construct(
        private readonly SpendingFPVReportService $spendingFPVReportService
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
}
