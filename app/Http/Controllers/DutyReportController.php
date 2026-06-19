<?php

namespace App\Http\Controllers;

use App\Services\DutyReportService;
use Illuminate\Http\Request;

class DutyReportController extends Controller
{
    public function __construct(private DutyReportService $dutyReportService)
    {
    }

    public function index()
    {
        $reportData = $this->dutyReportService->dutyReport();
        return view('admin.reports.duty-report');
    }
}
