<?php

namespace App\Http\Controllers;

use App\DTOs\CreatePositionDTO;
use App\DTOs\UpdatePositionDTO;
use App\Http\Requests\PositionStoreRequest;
use App\Http\Requests\PositionUpdateRequest;
use App\Services\PositionsAdminService;
use Illuminate\Support\Facades\Gate;

class PositionsController extends Controller
{
    public function __construct(private readonly PositionsAdminService $service)
    {
        $this->middleware(function ($request, $next) {
            if (Gate::denies('manage-positions')) {
                abort(403);
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $positions = $this->service->getAllPositions();
        return view('admin.positions.index', compact('positions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.positions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PositionStoreRequest $request)
    {
        $dto = CreatePositionDTO::fromRequest($request);
        $this->service->createPosition($dto);

        return redirect()->route('positions.index')
            ->with('success', 'Позицію успішно створено');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $position = $this->service->getPositionById($id);
        return view('admin.positions.show', compact('position'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        $position = $this->service->getPositionById($id);
        return view('admin.positions.edit', compact('position'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PositionUpdateRequest $request, int $id)
    {
        $dto = UpdatePositionDTO::fromRequest($request);
        $this->service->updatePosition($id, $dto);

        return redirect()->route('positions.index')
            ->with('success', 'Позицію успішно оновлено');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $this->service->deletePosition($id);

        return redirect()->route('positions.index')
            ->with('success', 'Позицію успішно видалено');
    }
}
