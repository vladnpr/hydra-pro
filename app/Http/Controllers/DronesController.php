<?php

namespace App\Http\Controllers;

use App\DTOs\CreateDroneDTO;
use App\Http\Requests\DroneStoreRequest;
use App\Services\DronesAdminService;

class DronesController extends Controller
{

    public function __construct(private readonly DronesAdminService $service)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $drones = $this->service->getAllDrones();
        return view('admin.drones.index', compact('drones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.drones.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DroneStoreRequest $request)
    {
        $dto = CreateDroneDTO::fromRequest($request);
        $this->service->createDrone($dto);

        return redirect()->route('drones.index')
            ->with('success', 'Дрон успішно створено');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
