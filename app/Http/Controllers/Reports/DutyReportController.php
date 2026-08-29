<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Presenters\Reports\DutyReportsListPresenter;
use App\Services\DutyReportsService;
use Illuminate\Http\Request;

class DutyReportController extends Controller
{
    public function __construct(readonly private DutyReportsService $dutyReportsService)
    {
    }

    public function index()
    {
        $presenter = new DutyReportsListPresenter();
        return view('admin.reports.duty.repots-list', ['presenter' => $presenter]);
    }
}
