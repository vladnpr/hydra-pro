<?php

namespace App\Http\Controllers;

use App\Services\CombatShiftsAdminService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(private readonly CombatShiftsAdminService $combatShiftsService)
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $stats = $this->combatShiftsService->getGlobalStats();
        $activeShift = $this->combatShiftsService->getActiveShiftByUserId(\Illuminate\Support\Facades\Auth::id());

        return view('admin.dashboard', compact('stats', 'activeShift'));
    }
}
