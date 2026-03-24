<?php

namespace App\Http\Controllers\AirDefence;

use App\Http\Controllers\Controller;
use App\Models\AirDefenceDrone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AirDefenceDronesController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Gate::denies('manage-air-defence-drones')) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function index()
    {
        $drones = AirDefenceDrone::all();
        return view('admin.air_defence.drones.index', compact('drones'));
    }

    public function create()
    {
        return view('admin.air_defence.drones.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'model' => 'nullable|string|max:255',
            'status' => 'required|string',
        ]);

        AirDefenceDrone::create($data);

        return redirect()->route('air-defence.drones.index')
            ->with('success', 'Дрон ППО успішно додано');
    }

    public function edit(int $id)
    {
        $drone = AirDefenceDrone::findOrFail($id);
        return view('admin.air_defence.drones.edit', compact('drone'));
    }

    public function update(Request $request, int $id)
    {
        $drone = AirDefenceDrone::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'model' => 'nullable|string|max:255',
            'status' => 'required|string',
        ]);

        $drone->update($data);

        return redirect()->route('air-defence.drones.index')
            ->with('success', 'Дрон ППО успішно оновлено');
    }

    public function destroy(int $id)
    {
        $drone = AirDefenceDrone::findOrFail($id);
        $drone->delete();

        return redirect()->route('air-defence.drones.index')
            ->with('success', 'Дрон ППО успішно видалено');
    }
}
