<?php

use Illuminate\Support\Facades\Route;
use Modules\Area\Http\Controllers\Admin\ProvinceController;
use Modules\Area\Http\Controllers\Admin\CityController;

Route::webSuperGroup('admin', function () {
  // Province
  Route::prefix('/provinces')->name('provinces.')->group(function () {
    Route::get('/', [ProvinceController::class, 'index'])->name('index');
    Route::get('/{province}', [ProvinceController::class, 'show'])->name('show');
    Route::post('/', [ProvinceController::class, 'store'])->name('store');
    Route::put('/{province}', [ProvinceController::class, 'update'])->name('update');
    Route::delete('/{province}', [ProvinceController::class, 'destroy'])->name('destroy');
  });

  // City
  Route::prefix('/cities')->name('cities.')->group(function () {
    Route::get('/', [CityController::class, 'index'])->name('index');
    Route::post('/', [CityController::class, 'store'])->name('store');
    Route::put('/{city}', [CityController::class, 'update'])->name('update');
    Route::delete('/{city}', [CityController::class, 'destroy'])->name('destroy');
  });
});
