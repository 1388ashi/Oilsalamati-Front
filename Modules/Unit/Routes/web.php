<?php
use Illuminate\Support\Facades\Route;
use Modules\Unit\Http\Controllers\Admin\UnitController;

Route::webSuperGroup('admin', function () {
    Route::get('/units', [UnitController::class,'index'])->name('units.index');
    Route::get('/units/create', [UnitController::class,'create'])->name('units.create');
    Route::post('/units', [UnitController::class,'store'])->name('units.store');
    Route::get('/units/{unit}/edit', [UnitController::class,'edit'])->name('units.edit');
    Route::patch('/units/{unit}', [UnitController::class,'update'])->name('units.update');
    Route::delete('/units/delete/{id}', [UnitController::class,'destroy'])->name('units.destroy');
});
