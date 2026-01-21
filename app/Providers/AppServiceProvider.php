<?php

namespace App\Providers;

use App\Repositories\Contracts\DroneRepositoryInterface;
use App\Repositories\Eloquent\EloquentDroneRepository;
use App\Repositories\Contracts\AmmunitionRepositoryInterface;
use App\Repositories\Eloquent\EloquentAmmunitionRepository;
use App\Repositories\Contracts\PositionRepositoryInterface;
use App\Repositories\Eloquent\EloquentPositionRepository;
use App\Repositories\Contracts\CombatShiftRepositoryInterface;
use App\Repositories\Eloquent\EloquentCombatShiftRepository;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Services\CombatShiftsAdminService;
use Illuminate\Support\Facades\Auth;
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
        $this->app->bind(PositionRepositoryInterface::class, EloquentPositionRepository::class);
        $this->app->bind(CombatShiftRepositoryInterface::class, EloquentCombatShiftRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('manage-users', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('manage-positions', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('manage-ammunition', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('manage-drones', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('access-combat', function (User $user) {
            return !$user->isGuest();
        });

        view()->composer('*', function ($view) {
            if (Auth::check()) {
                $service = $this->app->make(CombatShiftsAdminService::class);
                $view->with('globalActiveShift', $service->getActiveShiftByUserId(Auth::id()));
            }
        });
    }
}
