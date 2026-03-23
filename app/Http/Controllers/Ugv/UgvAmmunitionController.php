<?php

namespace App\Http\Controllers\Ugv;

use App\DTOs\CreateAmmunitionDTO;
use App\DTOs\UpdateAmmunitionDTO;
use App\Http\Requests\AmmunitionStoreRequest;
use App\Http\Requests\AmmunitionUpdateRequest;
use App\Http\Controllers\Controller;
use App\Services\AmmunitionAdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UgvAmmunitionController extends Controller
{
    public function __construct(private readonly AmmunitionAdminService $service)
    {
        $this->middleware(function ($request, $next) {
            if (Gate::denies('manage-ugv')) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function index() {
        $ammunition = $this->service->getAllAmmunition('ugv');
        return view('ugv.ammunition.index', compact('ammunition'));
    }

    public function show(int $id) {
        $ammunition = $this->service->getAmmunitionById($id);
        return view('ugv.ammunition.show', compact('ammunition'));
    }

    public function create() {
        return view('ugv.ammunition.create');
    }

    public function store(AmmunitionStoreRequest $request) {
        $dto = CreateAmmunitionDTO::fromRequest($request);
        $this->service->createAmmunition($dto);

        return redirect()->route('ugv.ammunition.index')
            ->with('success', 'Боєприпас успішно додано');
    }

    public function edit(int $id) {
        $ammunition = $this->service->getAmmunitionById($id);
        return view('ugv.ammunition.edit', compact('ammunition'));
    }

    public function update(AmmunitionUpdateRequest $request, int $id) {
        $dto = UpdateAmmunitionDTO::fromRequest($request);
        $this->service->updateAmmunition($id, $dto);

        return redirect()->route('ugv.ammunition.index')
            ->with('success', 'Боєприпас успішно оновлено');
    }

    public function destroy(int $id) {
        $this->service->deleteAmmunition($id);

        return redirect()->route('ugv.ammunition.index')
            ->with('success', 'Боєприпас успішно видалено');
    }
}
