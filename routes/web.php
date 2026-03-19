<?php

use App\Http\Controllers\DronesController;
use App\Http\Controllers\AmmunitionController;
use App\Http\Controllers\PositionsController;
use App\Http\Controllers\CombatShiftsController;
use Illuminate\Support\Facades\Route;

Auth::routes(['verify' => true]);

Route::get('/', [App\Http\Controllers\AdminController::class, 'index'])->name('home');
Route::group(['middleware' => ['auth', 'verified', 'can:access-combat'], 'prefix' => 'admin'], function () {
    Route::group(['prefix' => 'storage'], function () {
        Route::resource('drones', DronesController::class);
        Route::resource('ammunition', AmmunitionController::class);
    });
    Route::resource('positions', PositionsController::class);

    Route::group(['prefix' => 'recon', 'as' => 'recon.'], function () {
        Route::get('combat_shifts/{id}/flights-report', [\App\Http\Controllers\Recon\ReconCombatShiftsController::class, 'flightsReport'])->name('combat_shifts.flights_report')->where('id', '[0-9]+');
        Route::get('combat_shifts/{id}/report', [\App\Http\Controllers\Recon\ReconCombatShiftsController::class, 'report'])->name('combat_shifts.report')->where('id', '[0-9]+');
        Route::get('combat_shifts/{id}', [\App\Http\Controllers\Recon\ReconCombatShiftsController::class, 'show'])->name('combat_shifts.show')->where('id', '[0-9]+');
        Route::get('active-shift/flights-report', [\App\Http\Controllers\Recon\ReconCombatShiftsController::class, 'activeFlightsReport'])->name('combat_shifts.active_flights_report');

        Route::group(['middleware' => 'can:manage-recon'], function () {
            Route::get('drones/by-position/{positionId}', [\App\Http\Controllers\Recon\ReconDronesController::class, 'getByPosition'])->name('drones.by_position');
            Route::resource('drones', \App\Http\Controllers\Recon\ReconDronesController::class);
            Route::resource('ammunition', \App\Http\Controllers\Recon\ReconAmmunitionController::class);

            Route::post('combat_shifts/{id}/join', [\App\Http\Controllers\Recon\ReconCombatShiftsController::class, 'join'])->name('combat_shifts.join')->where('id', '[0-9]+');
            Route::post('combat_shifts/{id}/leave', [\App\Http\Controllers\Recon\ReconCombatShiftsController::class, 'leave'])->name('combat_shifts.leave')->where('id', '[0-9]+');
            Route::post('combat_shifts/{id}/finish', [\App\Http\Controllers\Recon\ReconCombatShiftsController::class, 'finish'])->name('combat_shifts.finish')->where('id', '[0-9]+');
            Route::post('combat_shifts/{id}/reopen', [\App\Http\Controllers\Recon\ReconCombatShiftsController::class, 'reopen'])->name('combat_shifts.reopen')->where('id', '[0-9]+');
            Route::resource('combat_shifts', \App\Http\Controllers\Recon\ReconCombatShiftsController::class)->except(['show']);

            Route::get('flights', [\App\Http\Controllers\Recon\ReconFlightController::class, 'index'])->name('flights.index');
            Route::post('flights', [\App\Http\Controllers\Recon\ReconFlightController::class, 'store'])->name('flights.store');
            Route::get('flights/{id}/edit', [\App\Http\Controllers\Recon\ReconFlightController::class, 'edit'])->name('flights.edit')->where('id', '[0-9]+');
            Route::put('flights/{id}', [\App\Http\Controllers\Recon\ReconFlightController::class, 'update'])->name('flights.update')->where('id', '[0-9]+');
            Route::post('flights/set-shift-type', [\App\Http\Controllers\Recon\ReconFlightController::class, 'setShiftType'])->name('flights.set_shift_type');
            Route::get('flights/{id}/download', [\App\Http\Controllers\Recon\ReconFlightController::class, 'downloadVideo'])->name('flights.download')->where('id', '[0-9]+');
            Route::delete('flights/{id}', [\App\Http\Controllers\Recon\ReconFlightController::class, 'destroy'])->name('flights.destroy')->where('id', '[0-9]+');
        });
    });

    Route::group(['prefix' => 'vampire', 'as' => 'vampire.'], function () {
        Route::get('combat_shifts/{id}/flights-report', [\App\Http\Controllers\Vampire\VampireCombatShiftController::class, 'flightsReport'])->name('combat_shifts.flights_report')->where('id', '[0-9]+');
        Route::get('combat_shifts/{id}/report', [\App\Http\Controllers\Vampire\VampireCombatShiftController::class, 'report'])->name('combat_shifts.report')->where('id', '[0-9]+');
        Route::get('combat_shifts/{id}', [\App\Http\Controllers\Vampire\VampireCombatShiftController::class, 'show'])->name('combat_shifts.show')->where('id', '[0-9]+');
        Route::get('active-shift/flights-report', [\App\Http\Controllers\Vampire\VampireCombatShiftController::class, 'activeFlightsReport'])->name('combat_shifts.active_flights_report');

        Route::group(['middleware' => 'can:manage-vampire'], function () {
            Route::get('drones/by-position/{positionId}', [\App\Http\Controllers\Vampire\VampireDronesController::class, 'getByPosition'])->name('drones.by_position');
            Route::resource('drones', \App\Http\Controllers\Vampire\VampireDronesController::class);
            Route::resource('ammunition', \App\Http\Controllers\Vampire\VampireAmmunitionController::class);

            Route::post('combat_shifts/{id}/join', [\App\Http\Controllers\Vampire\VampireCombatShiftController::class, 'join'])->name('combat_shifts.join')->where('id', '[0-9]+');
            Route::post('combat_shifts/{id}/leave', [\App\Http\Controllers\Vampire\VampireCombatShiftController::class, 'leave'])->name('combat_shifts.leave')->where('id', '[0-9]+');
            Route::post('combat_shifts/{id}/finish', [\App\Http\Controllers\Vampire\VampireCombatShiftController::class, 'finish'])->name('combat_shifts.finish')->where('id', '[0-9]+');
            Route::post('combat_shifts/{id}/reopen', [\App\Http\Controllers\Vampire\VampireCombatShiftController::class, 'reopen'])->name('combat_shifts.reopen')->where('id', '[0-9]+');
            Route::resource('combat_shifts', \App\Http\Controllers\Vampire\VampireCombatShiftController::class)->except(['show']);

            Route::get('flights', [\App\Http\Controllers\Vampire\VampireFlightController::class, 'index'])->name('flights.index');
            Route::post('flights', [\App\Http\Controllers\Vampire\VampireFlightController::class, 'store'])->name('flights.store');
            Route::get('flights/{id}/edit', [\App\Http\Controllers\Vampire\VampireFlightController::class, 'edit'])->name('flights.edit')->where('id', '[0-9]+');
            Route::put('flights/{id}', [\App\Http\Controllers\Vampire\VampireFlightController::class, 'update'])->name('flights.update')->where('id', '[0-9]+');
            Route::delete('flights/{id}', [\App\Http\Controllers\Vampire\VampireFlightController::class, 'destroy'])->name('flights.destroy')->where('id', '[0-9]+');
            Route::post('flight-plans', [\App\Http\Controllers\Vampire\VampireFlightController::class, 'storePlan'])->name('flight_plans.store');
            Route::get('flight-plans/{id}/edit', [\App\Http\Controllers\Vampire\VampireFlightController::class, 'editPlan'])->name('flight_plans.edit')->where('id', '[0-9]+');
            Route::put('flight-plans/{id}', [\App\Http\Controllers\Vampire\VampireFlightController::class, 'updatePlan'])->name('flight_plans.update')->where('id', '[0-9]+');
            Route::delete('flight-plans/{id}', [\App\Http\Controllers\Vampire\VampireFlightController::class, 'deletePlan'])->name('flight_plans.destroy')->where('id', '[0-9]+');
        });
    });

    Route::group(['middleware' => 'can:view-reports'], function () {
        Route::get('combat-shifts-active-reports', [CombatShiftsController::class, 'activeShiftsReports'])->name('combat_shifts.active_reports');
        Route::get('combat_shifts/{shiftId}/spending-fpv-report', [\App\Http\Controllers\SpendingFPVReportController::class, 'spendFPVReport'])->name('combat_shifts.spending_fpv_report')->where('shiftId', '[0-9]+');
        Route::get('combat_shifts/{id}/report', [CombatShiftsController::class, 'report'])->name('combat_shifts.report')->where('id', '[0-9]+');
        Route::get('combat_shifts/{id}/flights-report', [CombatShiftsController::class, 'flightsReport'])->name('combat_shifts.flights_report')->where('id', '[0-9]+');

        Route::group(['middleware' => 'can:manage-combat'], function () {
            Route::get('active-shift/flights-report', [CombatShiftsController::class, 'activeFlightsReport'])->name('combat_shifts.active_flights_report');
            Route::get('active-shift/remains-report', [CombatShiftsController::class, 'activeRemainsReport'])->name('combat_shifts.active_remains_report');
            Route::get('active-shift/active-spending-fpv-report', [\App\Http\Controllers\SpendingFPVReportController::class, 'activeSpendFPVReport'])->name('combat_shifts.active_spending_fpv_report');
        });
    });

    Route::group(['middleware' => 'can:manage-combat'], function () {
        Route::post('combat_shifts/{id}/join', [CombatShiftsController::class, 'join'])->name('combat_shifts.join')->where('id', '[0-9]+');
        Route::post('combat_shifts/{id}/leave', [CombatShiftsController::class, 'leave'])->name('combat_shifts.leave')->where('id', '[0-9]+');
        Route::post('combat_shifts/{id}/finish', [CombatShiftsController::class, 'finish'])->name('combat_shifts.finish')->where('id', '[0-9]+');
        Route::post('combat_shifts/{id}/reopen', [CombatShiftsController::class, 'reopen'])->name('combat_shifts.reopen')->where('id', '[0-9]+');

        Route::post('flight-operations', [App\Http\Controllers\FlightOperationsController::class, 'store'])->name('flight_operations.store');
        Route::get('flight-operations/{id}/edit', [App\Http\Controllers\FlightOperationsController::class, 'edit'])->name('flight_operations.edit')->where('id', '[0-9]+');
        Route::put('flight-operations/{id}', [App\Http\Controllers\FlightOperationsController::class, 'update'])->name('flight_operations.update')->where('id', '[0-9]+');
        Route::delete('flight-operations/{id}', [App\Http\Controllers\FlightOperationsController::class, 'destroy'])->name('flight_operations.destroy')->where('id', '[0-9]+');

        Route::get('flights/{id}/edit', [App\Http\Controllers\CombatShiftFlightsController::class, 'edit'])->name('flights.edit')->where('id', '[0-9]+');
        Route::put('flights/{id}', [App\Http\Controllers\CombatShiftFlightsController::class, 'update'])->name('flights.update')->where('id', '[0-9]+');
        Route::delete('flights/{id}', [App\Http\Controllers\CombatShiftFlightsController::class, 'destroy'])->name('flights.destroy')->where('id', '[0-9]+');
    });

    Route::resource('combat_shifts', CombatShiftsController::class);

    Route::get('flight-operations', [App\Http\Controllers\FlightOperationsController::class, 'index'])->name('flight_operations.index');
    Route::get('flight-operations/{id}/download', [App\Http\Controllers\FlightOperationsController::class, 'downloadVideo'])->name('flight_operations.download')->where('id', '[0-9]+');

    Route::group(['middleware' => 'can:manage-users'], function () {
        Route::resource('users', App\Http\Controllers\UsersController::class);
    });
});
