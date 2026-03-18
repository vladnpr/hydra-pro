<?php

namespace App\Http\Controllers\Vampire;

use App\DTOs\CreateAmmunitionDTO;
use App\DTOs\UpdateAmmunitionDTO;
use App\Http\Requests\AmmunitionStoreRequest;
use App\Http\Requests\AmmunitionUpdateRequest;
use App\Http\Controllers\Controller;
use App\Services\AmmunitionAdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VampireAmmunitionController extends Controller
{
    public function __construct(private readonly AmmunitionAdminService $service)
    {
        $this->middleware(function ($request, $next) {
            if (Gate::denies('manage-vampire')) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function index() {
        $ammunition = $this->service->getAllAmmunition('vampire');
        return view('vampire.ammunition.index', compact('ammunition'));
    }

    public function show(int $id) {
        $ammunition = $this->service->getAmmunitionById($id);
        return view('vampire.ammunition.show', compact('ammunition'));
    }

    public function create() {
        return view('vampire.ammunition.create');
    }

    public function store(AmmunitionStoreRequest $request) {
        $dto = CreateAmmunitionDTO::fromRequest($request);
        $this->service->createAmmunition($dto);

        return redirect()->route('vampire.ammunition.index')
            ->with('success', 'Боєприпас успішно додано');
    }

    public function edit(int $id) {
        $ammunition = $this->service->getAmmunitionById($id);
        return view('vampire.ammunition.edit', compact('ammunition'));
    }

    public function update(AmmunitionUpdateRequest $request, int $id) {
        $dto = UpdateAmmunitionDTO::fromRequest($request);
        $this->service->updateAmmunition($id, $dto);

        return redirect()->route('vampire.ammunition.index')
            ->with('success', 'Боєприпас успішно оновлено');
    }

    public function destroy(int $id) {
        $this->service->deleteAmmunition($id);

        return redirect()->route('vampire.ammunition.index')
            ->with('success', 'Боєприпас успішно видалено');
    }
}
