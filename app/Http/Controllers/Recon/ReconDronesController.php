<?php

namespace App\Http\Controllers\Recon;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReconDroneStoreRequest;
use App\Http\Requests\ReconDroneUpdateRequest;
use App\Services\ReconDroneAdminService;
use App\Repositories\Contracts\PositionRepositoryInterface;
use App\Enums\PositionTypesEnum;
use Illuminate\Support\Facades\Gate;

class ReconDronesController extends Controller
{
    public function __construct(
        private readonly ReconDroneAdminService $service,
        private readonly PositionRepositoryInterface $positionRepository
    ) {
        $this->middleware(function ($request, $next) {
            if (Gate::denies('manage-recon')) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function index()
    {
        $drones = $this->service->getAllDrones();
        return view('recon.drones.index', compact('drones'));
    }

    public function create()
    {
        $positions = $this->positionRepository->getActive(PositionTypesEnum::RECON->value);
        return view('recon.drones.create', compact('positions'));
    }

    public function store(ReconDroneStoreRequest $request)
    {
        try {
            $this->service->createDrone($request->validated());
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            return redirect()->back()
                ->withErrors(['serial_number' => 'Дрон з таким серійним номером вже існує в базі.'])
                ->withInput();
        }

        return redirect()->route('recon.drones.index')
            ->with('success', 'Дрон успішно додано');
    }

    public function show(int $id)
    {
        $drone = $this->service->getDroneById($id);
        return view('recon.drones.show', compact('drone'));
    }

    public function edit(int $id)
    {
        $drone = $this->service->getDroneById($id);
        $positions = $this->positionRepository->getActive(PositionTypesEnum::RECON->value);
        return view('recon.drones.edit', compact('drone', 'positions'));
    }

    public function update(ReconDroneUpdateRequest $request, int $id)
    {
        try {
            $this->service->updateDrone($id, $request->validated());
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            return redirect()->back()
                ->withErrors(['serial_number' => 'Дрон з таким серійним номером вже існує в базі.'])
                ->withInput();
        }

        return redirect()->route('recon.drones.index')
            ->with('success', 'Дрон успішно оновлено');
    }

    public function destroy(int $id)
    {
        $this->service->deleteDrone($id);

        return redirect()->route('recon.drones.index')
            ->with('success', 'Дрон успішно видалено');
    }

    public function getByPosition(int $positionId)
    {
        return response()->json($this->service->getDronesByPosition($positionId));
    }
}
