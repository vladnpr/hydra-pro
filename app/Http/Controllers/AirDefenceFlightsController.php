<?php

namespace App\Http\Controllers;

use App\Models\AirDefenceFlight;
use App\Models\AirDefenceDrone;
use App\Models\AirDefenceAmmunition;
use App\Models\Position;
use App\Enums\PositionTypesEnum;
use App\Http\Requests\AirDefenceFlightStoreRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AirDefenceFlightsController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Gate::denies('manage-air-defence')) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function index()
    {
        $flights = AirDefenceFlight::with(['drone', 'ammunition', 'position'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        return view('admin.air_defence.flights.index', compact('flights'));
    }

    public function create()
    {
        $positions = Position::where('type', PositionTypesEnum::AIR_DEFENCE->value)->get();
        $drones = AirDefenceDrone::where('status', 'active')->get();
        $ammunition = AirDefenceAmmunition::where('status', 'active')->get();
        return view('admin.air_defence.flights.create', compact('positions', 'drones', 'ammunition'));
    }

    public function store(AirDefenceFlightStoreRequest $request)
    {
        $data = $request->validated();
        $data['detonation'] = $request->has('detonation');

        AirDefenceFlight::create($data);

        return redirect()->route('air-defence.races.index')
            ->with('success', 'Виліт ППО успішно додано');
    }

    public function edit(int $id)
    {
        $flight = AirDefenceFlight::findOrFail($id);
        $positions = Position::where('type', PositionTypesEnum::AIR_DEFENCE->value)->get();
        $drones = AirDefenceDrone::all();
        $ammunition = AirDefenceAmmunition::all();
        return view('admin.air_defence.flights.edit', compact('flight', 'positions', 'drones', 'ammunition'));
    }

    public function update(AirDefenceFlightStoreRequest $request, int $id)
    {
        $flight = AirDefenceFlight::findOrFail($id);
        $data = $request->validated();
        $data['detonation'] = $request->has('detonation');

        $flight->update($data);

        return redirect()->route('air-defence.races.index')
            ->with('success', 'Виліт ППО успішно оновлено');
    }

    public function destroy(int $id)
    {
        $flight = AirDefenceFlight::findOrFail($id);
        $flight->delete();

        return redirect()->route('air-defence.races.index')
            ->with('success', 'Виліт ППО успішно видалено');
    }
}
