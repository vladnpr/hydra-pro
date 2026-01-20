<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface DroneRepositoryInterface
{
    /**
     * @return Collection<\App\Models\Drone>
     */
    public function all(): Collection;
}
