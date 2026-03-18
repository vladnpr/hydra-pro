<?php

namespace App\Http\Controllers\Vampire;

use App\Http\Controllers\Controller;
use App\Http\Requests\VampireDroneStoreRequest;
use App\Http\Requests\VampireDroneUpdateRequest;
use App\Services\VampireDroneAdminService;
use App\Repositories\Contracts\PositionRepositoryInterface;
use App\Enums\PositionTypesEnum;
use Illuminate\Support\Facades\Gate;

class VampireDronesController extends Controller
{
    public function __construct(
        private readonly VampireDroneAdminService $service,
        private readonly PositionRepositoryInterface $positionRepository
    ) {
        $this->middleware(function ($request, $next) {
            if (Gate::denies('manage-vampire')) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function index()
    {
        $drones = $this->service->getAllDrones();
        return view('vampire.drones.index', compact('drones'));
    }

    public function create()
    {
        $positions = $this->positionRepository->getActive(PositionTypesEnum::VAMPIRE->value);
        return view('vampire.drones.create', compact('positions'));
    }

    public function store(VampireDroneStoreRequest $request)
    {
        try {
            $this->service->createDrone($request->validated());
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            return redirect()->back()
                ->withErrors(['serial_number' => 'Дрон з таким серійним номером вже існує в базі.'])
                ->withInput();
        }

        return redirect()->route('vampire.drones.index')
            ->with('success', 'Дрон успішно додано');
    }

    public function show(int $id)
    {
        $drone = $this->service->getDroneById($id);
        return view('vampire.drones.show', compact('drone'));
    }

    public function edit(int $id)
    {
        $drone = $this->service->getDroneById($id);
        $positions = $this->positionRepository->getActive(PositionTypesEnum::VAMPIRE->value);
        return view('vampire.drones.edit', compact('drone', 'positions'));
    }

    public function update(VampireDroneUpdateRequest $request, int $id)
    {
        try {
            $this->service->updateDrone($id, $request->validated());
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            return redirect()->back()
                ->withErrors(['serial_number' => 'Дрон з таким серійним номером вже існує в базі.'])
                ->withInput();
        }

        return redirect()->route('vampire.drones.index')
            ->with('success', 'Дрон успішно оновлено');
    }

    public function destroy(int $id)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Тільки адміністратор може видаляти дрони.');
        }

        $this->service->deleteDrone($id);

        return redirect()->route('vampire.drones.index')
            ->with('success', 'Дрон успішно видалено');
    }

    public function getByPosition(int $positionId)
    {
        return response()->json($this->service->getDronesByPosition($positionId));
    }
}
