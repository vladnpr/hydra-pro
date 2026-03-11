<?php

namespace App\Http\Controllers\Recon;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReconDronesController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Gate::denies('manage-recon')) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function index() {
        return view('recon.drones.index');
    }

    public function show(int $id) {}
    public function create() {}
    public function store(Request $request) {}
    public function edit(int $id) {}
    public function update(Request $request, int $id) {}
    public function destroy(int $id) {}
}
