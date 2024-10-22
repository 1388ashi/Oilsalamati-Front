<?php

use Illuminate\Support\Facades\Route;
use Modules\Brand\Http\Controllers\Admin\BrandController;

Route::webSuperGroup('admin', function () {
    Route::prefix('/brands')->name('brands.')->group(function () {
      Route::get('/', [BrandController::class, 'index'])->name('index');
      Route::post('/', [BrandController::class, 'store'])->name('store');
      Route::patch('/{brand}', [BrandController::class, 'update'])->name('update');
      Route::delete('/{brand}', [BrandController::class, 'destroy'])->name('destroy');
    });
  });