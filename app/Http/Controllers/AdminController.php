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
        $this->middleware(['auth', 'verified']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user->isGuest()) {
            $admins = \App\Models\User::where('role', 'admin')->pluck('name');
            return view('admin.guest_dashboard', compact('admins'));
        }

        $period = $request->query('period');
        $dateFrom = $request->query('date_from', $request->query('from_date', $request->query('date')));
        $dateTo = $request->query('date_to', $request->query('to_date'));

        $activeShift = $this->combatShiftsService->getActiveShiftByUserId($user->id);
        $stats = $this->combatShiftsService->getDashboardStats($period, $dateFrom, $dateTo);

        return view('admin.dashboard', compact('stats', 'activeShift', 'period', 'dateFrom', 'dateTo'));
    }
}
