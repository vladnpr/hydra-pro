<?php

namespace App\Providers;

use App\Repositories\Contracts\DroneRepositoryInterface;
use App\Repositories\Eloquent\EloquentDroneRepository;
use App\Repositories\Contracts\AmmunitionRepositoryInterface;
use App\Repositories\Eloquent\EloquentAmmunitionRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(DroneRepositoryInterface::class, EloquentDroneRepository::class);
        $this->app->bind(AmmunitionRepositoryInterface::class, EloquentAmmunitionRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
