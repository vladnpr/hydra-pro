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
    Route::get('flights/{id}/edit', [App\Http\Controllers\CombatShiftFlightsController::class, 'edit'])->name('flights.edit');
    Route::put('flights/{id}', [App\Http\Controllers\CombatShiftFlightsController::class, 'update'])->name('flights.update');
    Route::delete('flights/{id}', [App\Http\Controllers\CombatShiftFlightsController::class, 'destroy'])->name('flights.destroy');
});
