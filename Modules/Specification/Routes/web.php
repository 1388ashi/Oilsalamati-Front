<?php

use Illuminate\Support\Facades\Route;
use Modules\Specification\Http\Controllers\Admin\SpecificationController;

Route::webSuperGroup('admin', function () {

  Route::prefix('/specifications')->name('specifications.')->group(function () {

    Route::get('/', [SpecificationController::class, 'index'])->name('index'); 

    Route::get('/create', [SpecificationController::class, 'create'])->name('create');

    Route::post('/', [SpecificationController::class, 'store'])->name('store');

    Route::get('/{specification}/edit', [SpecificationController::class, 'edit'])->name('edit');

    Route::put('/{specification}', [SpecificationController::class, 'update'])->name('update');

    Route::delete('/{specification}', [SpecificationController::class, 'destroy'])->name('destroy');

    Route::get('/{specification}', [SpecificationController::class, 'show'])->name('show');

  });
});