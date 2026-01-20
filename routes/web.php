<?php

use App\Http\Controllers\DronesController;
use App\Http\Controllers\AmmunitionController;
use App\Http\Controllers\PositionsController;
use App\Http\Controllers\CombatShiftsController;
use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get('/', [App\Http\Controllers\AdminController::class, 'index'])->name('home');
Route::group(['middleware' => 'auth', 'prefix' => 'admin'], function () {
    Route::group(['prefix' => 'storage'], function () {
        Route::resource('drones', DronesController::class);
        Route::resource('ammunition', AmmunitionController::class);
    });
    Route::resource('positions', PositionsController::class);
    Route::resource('combat_shifts', CombatShiftsController::class);
    Route::post('combat_shifts/{id}/join', [CombatShiftsController::class, 'join'])->name('combat_shifts.join');
    Route::post('combat_shifts/{id}/leave', [CombatShiftsController::class, 'leave'])->name('combat_shifts.leave');
    Route::get('combat_shifts/{id}/report', [CombatShiftsController::class, 'report'])->name('combat_shifts.report');
    Route::get('combat_shifts/{id}/flights-report', [CombatShiftsController::class, 'flightsReport'])->name('combat_shifts.flights_report');
    Route::get('flight-operations', [App\Http\Controllers\FlightOperationsController::class, 'index'])->name('flight_operations.index');
    Route::post('flight-operations', [App\Http\Controllers\FlightOperationsController::class, 'store'])->name('flight_operations.store');
    Route::get('flight-operations/{id}/edit', [App\Http\Controllers\FlightOperationsController::class, 'edit'])->name('flight_operations.edit');
    Route::put('flight-operations/{id}', [App\Http\Controllers\FlightOperationsController::class, 'update'])->name('flight_operations.update');
    Route::delete('flight-operations/{id}', [App\Http\Controllers\FlightOperationsController::class, 'destroy'])->name('flight_operations.destroy');

    Route::get('flights/{id}/edit', [App\Http\Controllers\CombatShiftFlightsController::class, 'edit'])->name('flights.edit');
    Route::put('flights/{id}', [App\Http\Controllers\CombatShiftFlightsController::class, 'update'])->name('flights.update');
    Route::delete('flights/{id}', [App\Http\Controllers\CombatShiftFlightsController::class, 'destroy'])->name('flights.destroy');
});
