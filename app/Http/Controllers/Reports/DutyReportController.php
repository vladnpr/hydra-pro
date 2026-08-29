<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DutyReportController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        return view('admin.reports.duty.repots-list');
    }
}
