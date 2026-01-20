<?php

use App\Http\Controllers\DronesController;
use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get('/', [App\Http\Controllers\AdminController::class, 'index'])->name('home');
Route::group(['middleware' => 'auth', 'prefix' => 'admin'], function () {
    Route::group(['prefix' => 'storage'], function () {
        Route::resource('drones', DronesController::class);
    });
});
