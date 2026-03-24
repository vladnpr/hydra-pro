<?php

namespace App\Http\Controllers\AirDefence;

use App\Http\Controllers\Controller;
use App\Models\AirDefenceAmmunition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AirDefenceAmmunitionController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Gate::denies('manage-air-defence-ammunition')) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function index()
    {
        $ammunition = AirDefenceAmmunition::all();
        return view('admin.air_defence.ammunition.index', compact('ammunition'));
    }

    public function create()
    {
        return view('admin.air_defence.ammunition.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'status' => 'required|string',
        ]);

        AirDefenceAmmunition::create($data);

        return redirect()->route('air-defence.ammunition.index')
            ->with('success', 'Боєприпас ППО успішно додано');
    }

    public function edit(int $id)
    {
        $ammunition = AirDefenceAmmunition::findOrFail($id);
        return view('admin.air_defence.ammunition.edit', compact('ammunition'));
    }

    public function update(Request $request, int $id)
    {
        $ammunition = AirDefenceAmmunition::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'status' => 'required|string',
        ]);

        $ammunition->update($data);

        return redirect()->route('air-defence.ammunition.index')
            ->with('success', 'Боєприпас ППО успішно оновлено');
    }

    public function destroy(int $id)
    {
        $ammunition = AirDefenceAmmunition::findOrFail($id);
        $ammunition->delete();

        return redirect()->route('air-defence.ammunition.index')
            ->with('success', 'Боєприпас ППО успішно видалено');
    }
}
