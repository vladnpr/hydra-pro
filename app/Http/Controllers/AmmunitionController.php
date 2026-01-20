<?php

namespace App\Http\Controllers;

use App\DTOs\CreateAmmunitionDTO;
use App\DTOs\UpdateAmmunitionDTO;
use App\Http\Requests\AmmunitionStoreRequest;
use App\Http\Requests\AmmunitionUpdateRequest;
use App\Services\AmmunitionAdminService;

class AmmunitionController extends Controller
{
    public function __construct(private readonly AmmunitionAdminService $service)
    {
    }

    public function index()
    {
        $ammunition = $this->service->getAllAmmunition();
        return view('admin.ammunition.index', compact('ammunition'));
    }

    public function create()
    {
        return view('admin.ammunition.create');
    }

    public function store(AmmunitionStoreRequest $request)
    {
        $dto = CreateAmmunitionDTO::fromRequest($request);
        $this->service->createAmmunition($dto);

        return redirect()->route('ammunition.index')
            ->with('success', 'Боєприпас успішно створено');
    }

    public function show(int $id)
    {
        $item = $this->service->getAmmunitionById($id);
        return view('admin.ammunition.show', compact('item'));
    }

    public function edit(int $id)
    {
        $item = $this->service->getAmmunitionById($id);
        return view('admin.ammunition.edit', compact('item'));
    }

    public function update(AmmunitionUpdateRequest $request, int $id)
    {
        $dto = UpdateAmmunitionDTO::fromRequest($request);
        $this->service->updateAmmunition($id, $dto);

        return redirect()->route('ammunition.index')
            ->with('success', 'Боєприпас успішно оновлено');
    }

    public function destroy(int $id)
    {
        $this->service->deleteAmmunition($id);

        return redirect()->route('ammunition.index')
            ->with('success', 'Боєприпас успішно видалено');
    }
}
