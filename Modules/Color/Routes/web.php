<?php
use Illuminate\Support\Facades\Route;
use Modules\Color\Http\Controllers\Admin\ColorController;

Route::webSuperGroup('admin', function () {
    Route::get('/colors', [ColorController::class,'index'])->name('colors.index');
    Route::post('/colors', [ColorController::class,'store'])->name('colors.store');
    Route::patch('/colors/{color}', [ColorController::class,'update'])->name('colors.update');
    Route::delete('/colors/delete/{color}', [ColorController::class,'destroy'])->name('colors.destroy');
});
