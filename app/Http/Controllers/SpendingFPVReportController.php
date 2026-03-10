<?php

namespace App\Http\Controllers;

use App\Services\SpendingFPVReportService;
use Illuminate\Http\Request;

class SpendingFPVReportController extends Controller
{
    public function __construct(
        private readonly SpendingFPVReportService $spendingFPVReportService
    )
    {
    }

    public function index()
    {
        $reportData = $this->spendingFPVReportService->getSpendingFPVReport(10);

    }
}
