<?php

namespace App\Providers;

use App\Repositories\Contracts\DroneRepositoryInterface;
use App\Repositories\Eloquent\EloquentDroneRepository;
use App\Repositories\Contracts\ReconDroneRepositoryInterface;
use App\Repositories\Eloquent\EloquentReconDroneRepository;
use App\Repositories\Contracts\AmmunitionRepositoryInterface;
use App\Repositories\Eloquent\EloquentAmmunitionRepository;
use App\Repositories\Contracts\PositionRepositoryInterface;
use App\Repositories\Eloquent\EloquentPositionRepository;
use App\Repositories\Contracts\CombatShiftRepositoryInterface;
use App\Repositories\Eloquent\EloquentCombatShiftRepository;
use App\Repositories\Contracts\VampireDroneRepositoryInterface;
use App\Repositories\Eloquent\EloquentVampireDroneRepository;
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
        $this->app->bind(ReconDroneRepositoryInterface::class, EloquentReconDroneRepository::class);
        $this->app->bind(AmmunitionRepositoryInterface::class, EloquentAmmunitionRepository::class);
        $this->app->bind(PositionRepositoryInterface::class, EloquentPositionRepository::class);
        $this->app->bind(CombatShiftRepositoryInterface::class, EloquentCombatShiftRepository::class);
        $this->app->bind(VampireDroneRepositoryInterface::class, EloquentVampireDroneRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('manage-vampire', function (User $user) {
            return $user->isAdmin() || $user->isVampire();
        });

        Gate::define('manage-recon', function (User $user) {
            return $user->isAdmin() || $user->isRecon();
        });

        Gate::define('manage-vampire-drones', function (User $user) {
            return $user->isAdmin() || $user->isVampire();
        });

        Gate::define('manage-vampire-ammunition', function (User $user) {
            return $user->isAdmin() || $user->isVampire();
        });

        Gate::define('manage-recon-ammunition', function (User $user) {
            return $user->isAdmin() || $user->isRecon();
        });

        Gate::define('manage-recon-drones', function (User $user) {
            return $user->isAdmin() || $user->isRecon();
        });

        Gate::define('manage-users', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('manage-positions', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('manage-fpv-ammunition', function (User $user) {
            return $user->isAdmin() || $user->isUser();
        });

        Gate::define('manage-fpv-drones', function (User $user) {
            return $user->isAdmin() || $user->isUser();
        });

        Gate::define('view-reports', function (User $user) {
            return $user->isAdmin() || $user->isManager() || $user->isUser() || $user->isRecon() || $user->isVampire();
        });

        Gate::define('access-combat', function (User $user) {
            return !$user->isGuest();
        });

        Gate::define('manage-combat', function (User $user) {
            return $user->isAdmin() || $user->isUser();
        });

        view()->composer('*', function ($view) {
            if (Auth::check()) {
                $service = $this->app->make(CombatShiftsAdminService::class);
                // Ми можемо кешувати активну зміну на час запиту, щоб не робити запит кожного разу, коли в'юшка рендериться
                static $activeShift = null;
                if ($activeShift === null) {
                    $activeShift = $service->getActiveShiftByUserId(Auth::id());
                }
                $view->with('globalActiveShift', $activeShift);
            }
        });
    }
}
