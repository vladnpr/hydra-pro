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
    public function index()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user->isGuest()) {
            $admins = \App\Models\User::where('role', 'admin')->pluck('name');
            return view('admin.guest_dashboard', compact('admins'));
        }

        $activeShift = $this->combatShiftsService->getActiveShiftByUserId($user->id);
        $stats = $this->combatShiftsService->getDashboardStats();

        return view('admin.dashboard', compact('stats', 'activeShift'));
    }
}
