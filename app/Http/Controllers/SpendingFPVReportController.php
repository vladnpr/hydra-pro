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
            return redirect()->route('recon.combat_shifts.show', $activeShift->id)
                ->with('info', 'Для RECON зміни використовуйте загальний звіт.');
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
