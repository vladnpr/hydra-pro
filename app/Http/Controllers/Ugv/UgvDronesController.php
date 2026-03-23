<?php

namespace App\Http\Controllers\Ugv;

use App\Http\Controllers\Controller;
use App\Http\Requests\UgvDroneStoreRequest;
use App\Http\Requests\UgvDroneUpdateRequest;
use App\Services\UgvDroneAdminService;
use App\Repositories\Contracts\PositionRepositoryInterface;
use App\Enums\PositionTypesEnum;
use Illuminate\Support\Facades\Gate;

class UgvDronesController extends Controller
{
    public function __construct(
        private readonly UgvDroneAdminService $service,
        private readonly PositionRepositoryInterface $positionRepository
    ) {
        $this->middleware(function ($request, $next) {
            if (Gate::denies('manage-ugv')) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function index()
    {
        $drones = $this->service->getAllDrones();
        return view('ugv.drones.index', compact('drones'));
    }

    public function create()
    {
        $positions = $this->positionRepository->getActive(PositionTypesEnum::UGV->value);
        return view('ugv.drones.create', compact('positions'));
    }

    public function store(UgvDroneStoreRequest $request)
    {
        try {
            $this->service->createDrone($request->validated());
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            return redirect()->back()
                ->withErrors(['serial_number' => 'НРК з таким серійним номером вже існує в базі.'])
                ->withInput();
        }

        return redirect()->route('ugv.drones.index')
            ->with('success', 'НРК успішно додано');
    }

    public function show(int $id)
    {
        $drone = $this->service->getDroneById($id);
        return view('ugv.drones.show', compact('drone'));
    }

    public function edit(int $id)
    {
        $drone = $this->service->getDroneById($id);
        $positions = $this->positionRepository->getActive(PositionTypesEnum::UGV->value);
        return view('ugv.drones.edit', compact('drone', 'positions'));
    }

    public function update(UgvDroneUpdateRequest $request, int $id)
    {
        try {
            $this->service->updateDrone($id, $request->validated());
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            return redirect()->back()
                ->withErrors(['serial_number' => 'НРК з таким серійним номером вже існує в базі.'])
                ->withInput();
        }

        return redirect()->route('ugv.drones.index')
            ->with('success', 'НРК успішно оновлено');
    }

    public function destroy(int $id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Тільки адміністратор може видаляти НРК.');
        }

        $this->service->deleteDrone($id);

        return redirect()->route('ugv.drones.index')
            ->with('success', 'НРК успішно видалено');
    }

    public function getByPosition(int $positionId)
    {
        return response()->json($this->service->getDronesByPosition($positionId));
    }
}
