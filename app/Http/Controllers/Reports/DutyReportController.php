<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Presenters\Reports\DutyReportsListPresenter;
use App\Services\DutyReports\DutyReportsService;

class DutyReportController extends Controller
{
    public function __construct(readonly private DutyReportsService $dutyReportsService)
    {
    }

    public function index()
    {
        $presenter = new DutyReportsListPresenter();
        $activeDuties = $this->dutyReportsService->getReports();
        return view('admin.reports.duty.repots-list', ['presenter' => $presenter]);
    }
}
