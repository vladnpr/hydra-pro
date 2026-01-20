<?php

namespace App\Providers;

use App\Repositories\Contracts\DroneRepositoryInterface;
use App\Repositories\Eloquent\EloquentDroneRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(DroneRepositoryInterface::class, EloquentDroneRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
