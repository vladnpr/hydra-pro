<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Presenters\Reports\DutyReportsListPresenter;
use App\Services\DutyReports\DutyReportsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DutyReportController extends Controller
{
    public function __construct(readonly private DutyReportsService $dutyReportsService)
    {
        $this->middleware(function ($request, $next) {
            if (Gate::denies('view-duty-reports')) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function reportList(Request $request)
    {
        $timezone = config('app.timezone');
        $from = Carbon::today($timezone)->setTime(4, 0);
        $to = Carbon::today($timezone)->setTime(21, 30);

        if ($request->has('from') && $request->has('to')) {
            $from = Carbon::parse($request->from);
            $to = Carbon::parse($request->to);
        }

        $reports = $this->dutyReportsService->getReportsData($from, $to);

        return view('admin.reports.duty.duty-report', [
            'presenter' => new DutyReportsListPresenter($from, $to, $reports)
        ]);
    }
}
