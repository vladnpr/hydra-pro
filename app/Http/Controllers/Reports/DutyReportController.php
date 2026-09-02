<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Presenters\Reports\DutyReportsListPresenter;
use App\Services\DutyReports\DutyReportsService;
use Carbon\Carbon;

class DutyReportController extends Controller
{
    public function __construct(readonly private DutyReportsService $dutyReportsService)
    {
    }

    public function index()
    {
        $presenter = new DutyReportsListPresenter();

        $from = Carbon::now()->subDays(10);
        $to = Carbon::now();

        $activeDuties = $this->dutyReportsService->getReports($from, $to);

        return view('admin.reports.duty.repots-list', ['presenter' => $presenter]);
    }
}
